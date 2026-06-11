@if ($data->payment_status == 'Partial')
<span class="badge badge-warning">
    Sebagian
</span>
@elseif ($data->payment_status == 'Paid')
<span class="badge badge-success">
    Lunas
</span>
@else
<span class="badge badge-danger">
    Belum Bayar
</span>
@endif