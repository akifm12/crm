<tr class="border-t border-gray-100">
    <td class="px-2 py-1">
        <select name="item_ids[]" data-role="item-select" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
            <option value="">— none —</option>
            @foreach($items as $itm)
            <option value="{{ $itm->id }}">{{ $itm->name }}{{ $itm->code ? ' [' . $itm->code . ']' : '' }}</option>
            @endforeach
        </select>
    </td>
    <td class="px-2 py-1">
        <input type="text" name="descriptions[]" data-role="desc" class="w-full text-xs border border-gray-200 rounded px-2 py-1" placeholder="Description">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="quantities[]" data-role="qty" step="0.0001" min="0" value="1" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))">
    </td>
    <td class="px-2 py-1">
        <select name="units[]" data-role="unit" class="w-full text-xs border border-gray-200 rounded px-1 py-1">
            <option value="pcs">pcs</option>
            <option value="grams">g</option>
            <option value="oz">oz</option>
            <option value="kg">kg</option>
        </select>
    </td>
    <td class="px-2 py-1">
        <input type="number" name="unit_prices[]" data-role="price" step="0.0001" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))">
    </td>
    <td class="px-2 py-1">
        <input type="number" name="vat_rates[]" data-role="vat" step="0.01" min="0" max="100" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))">
    </td>
    <td class="px-2 py-1 text-right font-mono text-xs text-gray-600">
        <span data-role="line-total">0.00</span>
    </td>
    <td class="px-2 py-1 text-center">
        <button type="button" onclick="this.closest('tr').remove(); recalcTotals()" class="text-red-400 hover:text-red-600 text-xs">×</button>
    </td>
</tr>
