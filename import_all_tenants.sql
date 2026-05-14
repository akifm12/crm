-- ============================================================
-- Bulk import for all remaining tenants
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';
SET collation_connection = utf8mb4_unicode_ci;


-- ============================================================
-- Motiwala Gold Jewellers LLC
-- ============================================================
-- ============================================================
-- Import clients for Motiwala Gold Jewellers LLC
-- v20_tenant_id = 23, slug = mwgj
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'mwgj' LIMIT 1);
SET @v20_tenant_id = 23;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _mwgj_entity_map;
CREATE TEMPORARY TABLE _mwgj_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _mwgj_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _mwgj_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _mwgj_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _mwgj_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Real Jewellers And Motiwala Gold LLC
-- ============================================================
-- ============================================================
-- Import clients for Real Jewellers And Motiwala Gold LLC
-- v20_tenant_id = 14, slug = real
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'real' LIMIT 1);
SET @v20_tenant_id = 14;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _real_entity_map;
CREATE TEMPORARY TABLE _real_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _real_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _real_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _real_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _real_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Dazzling Diamonds FZCO
-- ============================================================
-- ============================================================
-- Import clients for Dazzling Diamonds FZCO
-- v20_tenant_id = 26, slug = dazzling
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'dazzling' LIMIT 1);
SET @v20_tenant_id = 26;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _dazzling_entity_map;
CREATE TEMPORARY TABLE _dazzling_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _dazzling_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _dazzling_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _dazzling_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _dazzling_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Motiwala Gold Trading LLC
-- ============================================================
-- ============================================================
-- Import clients for Motiwala Gold Trading LLC
-- v20_tenant_id = 2, slug = mwgt
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'mwgt' LIMIT 1);
SET @v20_tenant_id = 2;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _mwgt_entity_map;
CREATE TEMPORARY TABLE _mwgt_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _mwgt_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _mwgt_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _mwgt_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _mwgt_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Fortune Ocean Gold and Jewelry LLC
-- ============================================================
-- ============================================================
-- Import clients for Fortune Ocean Gold and Jewelry LLC
-- v20_tenant_id = 11, slug = fortune
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'fortune' LIMIT 1);
SET @v20_tenant_id = 11;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _fortune_entity_map;
CREATE TEMPORARY TABLE _fortune_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _fortune_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _fortune_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _fortune_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _fortune_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- El Torro Real Estate Brokerage LLC
-- ============================================================
-- ============================================================
-- Import clients for El Torro Real Estate Brokerage LLC
-- v20_tenant_id = 16, slug = eltorro
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'eltorro' LIMIT 1);
SET @v20_tenant_id = 16;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _eltorro_entity_map;
CREATE TEMPORARY TABLE _eltorro_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _eltorro_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _eltorro_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _eltorro_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _eltorro_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Fajar Al Noor Gold Trading LLC
-- ============================================================
-- ============================================================
-- Import clients for Fajar Al Noor Gold Trading LLC
-- v20_tenant_id = 30, slug = fajar
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'fajar' LIMIT 1);
SET @v20_tenant_id = 30;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _fajar_entity_map;
CREATE TEMPORARY TABLE _fajar_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _fajar_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _fajar_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _fajar_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _fajar_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Alyasameen Aldahabe Gold Trading LLC
-- ============================================================
-- ============================================================
-- Import clients for Alyasameen Aldahabe Gold Trading LLC
-- v20_tenant_id = 25, slug = ayad
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'ayad' LIMIT 1);
SET @v20_tenant_id = 25;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _ayad_entity_map;
CREATE TEMPORARY TABLE _ayad_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _ayad_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _ayad_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _ayad_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _ayad_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Motiwala Gold And Precious Stones Industry FZC
-- ============================================================
-- ============================================================
-- Import clients for Motiwala Gold And Precious Stones Industry FZC
-- v20_tenant_id = 27, slug = refinery
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'refinery' LIMIT 1);
SET @v20_tenant_id = 27;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _refinery_entity_map;
CREATE TEMPORARY TABLE _refinery_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _refinery_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _refinery_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _refinery_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _refinery_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- Urban Signature Real Estate
-- ============================================================
-- ============================================================
-- Import clients for Urban Signature Real Estate
-- v20_tenant_id = 4, slug = usre
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'usre' LIMIT 1);
SET @v20_tenant_id = 4;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _usre_entity_map;
CREATE TEMPORARY TABLE _usre_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _usre_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _usre_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _usre_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _usre_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



-- ============================================================
-- ComTech FZCO
-- ============================================================
-- ============================================================
-- Import clients for ComTech FZCO
-- v20_tenant_id = 15, slug = comt
-- Deduplicates by passport_number + dob, then name + dob
-- Converts duplicate visits to transaction history
-- ============================================================


-- Get portal tenant ID for Prince Jewellers
SET @tenant_id = (SELECT id FROM bluearrow_portal.tenants WHERE slug = 'comt' LIMIT 1);
SET @v20_tenant_id = 15;

SELECT CONCAT('Importing for tenant_id: ', @tenant_id) as status;

-- ============================================================
-- STEP 1: Import deduplicated individual clients
-- Keep first occurrence (lowest ID) per passport+dob group
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, full_name, nationality, dob,
    passport_number, passport_expiry, phone,
    country_of_incorporation, source_of_funds,
    status, created_at, updated_at
)
SELECT
    @tenant_id,
    'individual',
    ic.name,
    ic.nationality,
    ic.birthdate,
    ic.passport_number,
    ic.passport_expiry_date,
    ic.telephone_number,
    ic.country_of_residence,
    JSON_ARRAY('salary'),
    'active',
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    NOW()
FROM v20.tenant_individual_clients ic
WHERE ic.tenant_id = @v20_tenant_id
  -- Only import the FIRST occurrence per passport+dob
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  -- Also not already imported
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND bc.dob = ic.birthdate
  );

SELECT CONCAT('Individual clients imported: ', ROW_COUNT()) as status;

-- Also handle name+dob duplicates (different passport numbers for same person)
-- Find any remaining that match by name+dob to existing records
-- (these will just become transactions on the existing record)

-- ============================================================
-- STEP 2: Import corporate entity clients
-- ============================================================

INSERT INTO bluearrow_portal.bullion_clients (
    tenant_id, client_type, company_name,
    trade_license_no, trade_license_issue, trade_license_expiry,
    legal_form, country_of_incorporation, business_activity,
    email, phone, website, registered_address, nature_of_business,
    source_of_funds, trn_number, status, created_at, updated_at
)
SELECT
    @tenant_id,
    CASE ec.client_type WHEN 'local' THEN 'corporate_local' WHEN 'import' THEN 'corporate_import' WHEN 'export' THEN 'corporate_export' ELSE 'corporate_local' END,
    ec.company_name, ec.trade_license_number, ec.license_issue_date, ec.license_expiry_date,
    ec.legal_status, ec.country, ec.nature_of_business,
    ec.contact_email, ec.contact_phone, ec.company_website, ec.registered_address, ec.nature_of_business,
    JSON_ARRAY('trading_revenue'), ec.vat_number, 'active',
    NULLIF(ec.created_at, '0000-00-00 00:00:00'), NOW()
FROM v20.tenant_entity_clients ec
WHERE ec.tenant_id = @v20_tenant_id
  AND NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.bullion_clients bc
    WHERE bc.tenant_id = @tenant_id
      AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
  );

SELECT CONCAT('Corporate clients imported: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 3: Convert duplicate visits to transactions
-- For each duplicate individual (same passport+dob, not the first),
-- create a transaction record on the canonical client
-- ============================================================

INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    CONCAT('Imported from previous system — visit on ',
        COALESCE(NULLIF(ic.created_at, '0000-00-00'), 'unknown date')),
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  -- Only the NON-first occurrences (duplicates)
  AND ic.id != (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('Duplicate visits converted to transactions: ', ROW_COUNT()) as status;

-- Also import the FIRST visit as a transaction if it has invoice data
INSERT INTO bluearrow_portal.client_transactions (
    bullion_client_id, tenant_id, visit_date,
    invoice_number, invoice_amount, notes, created_at, updated_at
)
SELECT
    bc.id,
    @tenant_id,
    COALESCE(NULLIF(ic.created_at, '0000-00-00'), NOW()),
    ic.invoice_number,
    ic.invoice_amount,
    'Initial visit — imported from previous system',
    NOW(), NOW()
FROM v20.tenant_individual_clients ic
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
    AND bc.dob = ic.birthdate
WHERE ic.tenant_id = @v20_tenant_id
  AND ic.id = (
    SELECT MIN(id) FROM v20.tenant_individual_clients
    WHERE tenant_id = @v20_tenant_id
      AND CONVERT(passport_number USING utf8mb4) = CONVERT(ic.passport_number USING utf8mb4)
      AND birthdate = ic.birthdate
  )
  AND (ic.invoice_number IS NOT NULL OR ic.invoice_amount IS NOT NULL);

SELECT CONCAT('First visit transactions created: ', ROW_COUNT()) as status;

-- ============================================================
-- STEP 4: Import entity client shareholders, UBOs, signatories
-- ============================================================

-- Build entity client map for this tenant
DROP TEMPORARY TABLE IF EXISTS _comt_entity_map;
CREATE TEMPORARY TABLE _comt_entity_map (v20_entity_id INT, portal_client_id INT);

INSERT INTO _comt_entity_map (v20_entity_id, portal_client_id)
SELECT ec.id, bc.id
FROM v20.tenant_entity_clients ec
JOIN bluearrow_portal.bullion_clients bc
    ON bc.tenant_id = @tenant_id
    AND CONVERT(bc.company_name USING utf8mb4) = CONVERT(ec.company_name USING utf8mb4)
WHERE ec.tenant_id = @v20_tenant_id;

-- Shareholders
INSERT INTO bluearrow_portal.client_shareholders (
    bullion_client_id, shareholder_type, name, nationality, dob, passport_number, is_ubo, created_at, updated_at
)
SELECT em.portal_client_id, 'individual', sh.name, sh.nationality, sh.dob, sh.passport_no, 0, NOW(), NOW()
FROM v20.tenant_entity_client_shareholders sh
JOIN _comt_entity_map em ON em.v20_entity_id = sh.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_shareholders cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.name USING utf8mb4) = CONVERT(sh.name USING utf8mb4)
);

SELECT CONCAT('Shareholders imported: ', ROW_COUNT()) as status;

-- UBOs
INSERT INTO bluearrow_portal.client_ubos (
    bullion_client_id, full_name, nationality, dob, passport_number, passport_expiry, created_at, updated_at
)
SELECT em.portal_client_id, u.name, u.nationality, u.dob, u.passport_no, u.passport_expiry, NOW(), NOW()
FROM v20.tenant_entity_client_ubos u
JOIN _comt_entity_map em ON em.v20_entity_id = u.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_ubos cu
    WHERE cu.bullion_client_id = em.portal_client_id AND CONVERT(cu.full_name USING utf8mb4) = CONVERT(u.name USING utf8mb4)
);

SELECT CONCAT('UBOs imported: ', ROW_COUNT()) as status;

-- Signatories
INSERT INTO bluearrow_portal.client_signatories (
    bullion_client_id, full_name, position, phone, email, created_at, updated_at
)
SELECT em.portal_client_id, mg.name, mg.position, mg.contact_phone, mg.contact_email, NOW(), NOW()
FROM v20.tenant_entity_client_management mg
JOIN _comt_entity_map em ON em.v20_entity_id = mg.entity_client_id
WHERE NOT EXISTS (
    SELECT 1 FROM bluearrow_portal.client_signatories cs
    WHERE cs.bullion_client_id = em.portal_client_id AND CONVERT(cs.full_name USING utf8mb4) = CONVERT(mg.name USING utf8mb4)
);

SELECT CONCAT('Signatories imported: ', ROW_COUNT()) as status;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Individual clients'  as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type = 'individual'
UNION SELECT 'Corporate clients',   COUNT(*) FROM bluearrow_portal.bullion_clients WHERE tenant_id = @tenant_id AND client_type != 'individual'
UNION SELECT 'Transactions',        COUNT(*) FROM bluearrow_portal.client_transactions WHERE tenant_id = @tenant_id
UNION SELECT 'Shareholders',        COUNT(*) FROM bluearrow_portal.client_shareholders cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'UBOs',                COUNT(*) FROM bluearrow_portal.client_ubos cu JOIN bluearrow_portal.bullion_clients bc ON bc.id = cu.bullion_client_id WHERE bc.tenant_id = @tenant_id
UNION SELECT 'Signatories',         COUNT(*) FROM bluearrow_portal.client_signatories cs JOIN bluearrow_portal.bullion_clients bc ON bc.id = cs.bullion_client_id WHERE bc.tenant_id = @tenant_id;



SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- GRAND TOTAL VERIFICATION
-- ============================================================
SELECT 'Total individual clients' as item, COUNT(*) as count FROM bluearrow_portal.bullion_clients WHERE client_type = 'individual'
UNION SELECT 'Total corporate clients', COUNT(*) FROM bluearrow_portal.bullion_clients WHERE client_type != 'individual'
UNION SELECT 'Total transactions', COUNT(*) FROM bluearrow_portal.client_transactions
UNION SELECT 'Total shareholders', COUNT(*) FROM bluearrow_portal.client_shareholders
UNION SELECT 'Total UBOs', COUNT(*) FROM bluearrow_portal.client_ubos
UNION SELECT 'Total signatories', COUNT(*) FROM bluearrow_portal.client_signatories;
