{{-- Parcel detail card — used in overview side panel --}}
<table style="width:100%;border-collapse:collapse;font-size:12px">
    <tr><td style="color:#888;padding:4px 0;width:120px">Parcel reference</td><td style="font-weight:700;color:#1a3d1a">{{ $p['ref'] }}</td></tr>
    <tr><td style="color:#888;padding:4px 0">Municipality</td><td style="font-weight:700">{{ $p['municipality'] }}</td></tr>
    <tr><td style="color:#888;padding:4px 0">Barangay</td><td style="font-weight:700">{{ $p['barangay'] }}</td></tr>
    <tr><td style="color:#888;padding:4px 0">Area</td><td style="font-weight:700">{{ $p['area_ha'] }} ha</td></tr>
    <tr><td style="color:#888;padding:4px 0">Status</td><td><span class="badge badge-{{ $p['status'] }}">{{ match($p['status']){'validated'=>'Validated','for_review'=>'For Review','pending'=>'Pending',default=>'—'} }}</span></td></tr>
    <tr><td style="color:#888;padding:4px 0">Assigned unit</td><td style="font-weight:700">GIS Records Unit</td></tr>
    <tr><td style="color:#888;padding:4px 0">Last updated</td><td style="font-weight:700">{{ $p['updated']->format('M j, Y') }}</td></tr>
</table>

<div style="margin-top:10px;font-size:12px;font-weight:700;color:#333">Monitoring timeline</div>
<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #f5f5f5;color:#555"><span>Parcel record validated</span><span style="color:#1a3d1a;font-weight:600">Step 3</span></div>
<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #f5f5f5;color:#555"><span>Location record reviewed</span><span style="color:#1a3d1a;font-weight:600">Step 2</span></div>
<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;color:#555"><span>Parcel monitoring entry received</span><span style="color:#1a3d1a;font-weight:600">Step 1</span></div>

<div style="margin-top:10px;font-size:12px;font-weight:700;color:#333">Linked GIS Document References</div>
<div style="display:flex;justify-content:space-between;padding:5px 0;font-size:11.5px;border-bottom:1px solid #f5f5f5">
    <div><div style="font-weight:700;color:#1a3d1a">GIS-DOC-001</div><div style="color:#888">Parcel Reference Sheet</div></div>
    <span class="badge badge-validated">Verified</span>
</div>
<div style="display:flex;justify-content:space-between;padding:5px 0;font-size:11.5px">
    <div><div style="font-weight:700;color:#1a3d1a">GIS-DOC-002</div><div style="color:#888">Boundary / Location Attachment</div></div>
    @if($p['attachments'] > 0)
    <span class="badge badge-validated">Verified</span>
    @else
    <span class="badge badge-pending">Pending</span>
    @endif
</div>
<div style="font-size:10.5px;color:#aaa;margin-top:6px;font-style:italic">Demo document references only; no official documents are attached.</div>
