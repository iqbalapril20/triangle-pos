@if ($data->status == 'Pending')
<span class="badge badge-info">
    Tertunda
</span>
@elseif ($data->status == 'Shipped')
<span class="badge badge-primary">
    Dikirim
</span>
@else
<span class="badge badge-success">
    Selesai
</span>
@endif