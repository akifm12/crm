<?php

namespace App\Services;

use App\Models\Tenant;
use RuntimeException;
use ZipArchive;

/**
 * Fills a policy-manual .docx template with a tenant's own details.
 *
 * The templates (resources/policy-manuals/...) mark every company-specific item as a
 * {{PLACEHOLDER}} in a highlighted run. A value is written into that run and the
 * highlight is removed; a placeholder with no value is left as it is, still highlighted,
 * so unfinished fields are obvious in the generated document.
 */
class PolicyManualGenerator
{
    /** key => [label, hint, type]. Order is the order shown on the form. */
    public const FIELDS = [
        'Company' => [
            'COMPANY_NAME'         => ['Full legal name', 'As on the trade licence', 'text'],
            'COMPANY_SHORT'        => ['Short name', 'Used throughout the text', 'text'],
            'TRADE_LICENSE_NO'     => ['Trade license number', '', 'text'],
            'LICENSING_AUTHORITY'  => ['Licensing authority', 'e.g. Dubai Department of Economy and Tourism', 'text'],
            'GOAML_ENTITY_ID'      => ['goAML entity ID', '', 'text'],
            'SENIOR_MANAGEMENT'    => ['Senior management', 'Who holds senior-management authority, e.g. the Managing Director', 'text'],
        ],
        'MLRO and deputy' => [
            'MLRO_NAME'            => ['MLRO name', '', 'text'],
            'MLRO_EMAIL'           => ['MLRO email', 'Use an official address, not a personal one', 'text'],
            'MLRO_PHONE'           => ['MLRO phone', '', 'text'],
            'MLRO_APPOINTMENT'     => ['MLRO appointment', '', 'select'],
            'DEPUTY_MLRO_NAME'     => ['Deputy MLRO name', '', 'text'],
            'DEPUTY_MLRO_EMAIL'    => ['Deputy MLRO email', '', 'text'],
            'DEPUTY_MLRO_PHONE'    => ['Deputy MLRO phone', '', 'text'],
        ],
        'Supply chain policy' => [
            'WEBSITE'              => ['Website', 'Where the policy statement and grievance mechanism are published', 'text'],
            'GRIEVANCE_CONTACT'    => ['Grievance contact', 'Confidential channel for concerns (email, hotline or form)', 'text'],
        ],
        'Controls and document' => [
            'SCREENING_TOOL'       => ['Screening tool', 'Sanctions / PEP / adverse media tool in use', 'text'],
            'RETENTION_YEARS'      => ['Record retention (years)', 'Not less than 5', 'text'],
            'VERSION'              => ['Manual version', '', 'text'],
            'ISSUE_DATE'           => ['Issue date', '', 'text'],
            'APPROVER'             => ['Approved by', '', 'text'],
        ],
    ];

    public const APPOINTMENT_OPTIONS = [
        'an employee of the company',
        'an external appointee under a written service agreement',
    ];

    /** Template files, relative to resources/policy-manuals. */
    public const TEMPLATES = [
        'aml-cft-policy' => 'aml-cft-cpf/AML-CFT-CPF-Manual-Template-v2.3.2.docx',
        'aml-real-estate' => 'aml-sectors/AML-CFT-CPF-Manual-Real-Estate-Template-v1.docx',
        'aml-csp' => 'aml-sectors/AML-CFT-CPF-Manual-CSP-Template-v1.docx',
        'supply-chain-policy' => 'supply-chain/Supply-Chain-Policy-Template-v1.1.docx',
    ];

    public static function templatePath(string $manual): ?string
    {
        $rel = self::TEMPLATES[$manual] ?? null;
        $path = $rel ? resource_path('policy-manuals/' . $rel) : null;

        return $path && is_file($path) ? $path : null;
    }

    public static function keys(): array
    {
        $keys = [];
        foreach (self::FIELDS as $group) {
            $keys = array_merge($keys, array_keys($group));
        }

        return $keys;
    }

    /** Defaults from the tenant record, overlaid with whatever was saved for the manual. */
    public static function values(Tenant $tenant): array
    {
        $short = trim(preg_replace('/[\s,]+(L\.?L\.?C\.?|FZ-?LLC|FZ-?CO|FZE|FZCO|LTD\.?|LIMITED|INC\.?)\s*$/i', '', (string) $tenant->name));

        $defaults = [
            'COMPANY_NAME'      => (string) $tenant->name,
            'COMPANY_SHORT'     => $short,
            'TRADE_LICENSE_NO'  => (string) ($tenant->trade_license_no ?? ''),
            'MLRO_NAME'         => (string) ($tenant->mlro_name ?? ''),
            'MLRO_EMAIL'        => (string) ($tenant->mlro_email ?? ''),
            'MLRO_PHONE'        => (string) ($tenant->mlro_phone ?? ''),
            'MLRO_APPOINTMENT'  => self::APPOINTMENT_OPTIONS[0],
            'SENIOR_MANAGEMENT' => 'the Managing Director',
            'RETENTION_YEARS'   => '5',
            'VERSION'           => '1.0',
            'ISSUE_DATE'        => now()->format('j F Y'),
            'APPROVER'          => 'Managing Director',
        ];

        $saved = (array) ($tenant->settings['policy_profile'] ?? []);
        if (isset($saved['TRADE_LICENCE_NO']) && ! isset($saved['TRADE_LICENSE_NO'])) {
            $saved['TRADE_LICENSE_NO'] = $saved['TRADE_LICENCE_NO']; // key renamed to US spelling
        }
        $out = [];
        foreach (self::keys() as $k) {
            $v = $saved[$k] ?? null;
            $out[$k] = ($v !== null && $v !== '') ? (string) $v : ($defaults[$k] ?? '');
        }

        return $out;
    }

    /** Returns the filled .docx as a path to a temporary file. */
    public function generate(string $templatePath, array $values): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pm');
        if (! copy($templatePath, $tmp)) {
            throw new RuntimeException('Could not copy the manual template.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            throw new RuntimeException('The manual template is not a valid .docx.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $name)) {
                continue;
            }
            $xml = $zip->getFromIndex($i);
            if ($name === 'word/document.xml') {
                $xml = $this->stripTemplateGuide($xml);
            }
            $zip->addFromString($name, $this->fill($xml, $values));
        }
        $zip->close();

        return $tmp;
    }

    /** The "template guide" page at the front is for whoever edits the template, not for the client. */
    public function stripTemplateGuide(string $xml): string
    {
        $pattern = '#<w:p>(?:(?!</w:p>).)*?TEMPLATE GUIDE.*?End of template guide\.(?:(?!</w:p>).)*?</w:p>'
            . '(?:<w:p><w:r><w:br w:type="page"/></w:r></w:p>)?#s';

        return preg_replace($pattern, '', $xml, 1) ?? $xml;
    }

    public function fill(string $xml, array $values): string
    {
        return preg_replace_callback('#<w:r>(?:(?!</w:r>).)*?</w:r>#s', function ($m) use ($values) {
            $run = $m[0];
            if (! preg_match('#<w:t[^>]*>\{\{([A-Z0-9_]+)\}\}</w:t>#', $run, $t)) {
                return $run;
            }
            $value = trim((string) ($values[$t[1]] ?? ''));
            if ($value === '') {
                return $run; // leave the highlighted placeholder so the gap is visible
            }
            $run = str_replace('{{' . $t[1] . '}}', htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8'), $run);

            return str_replace('<w:highlight w:val="yellow"/>', '', $run);
        }, $xml);
    }
}
