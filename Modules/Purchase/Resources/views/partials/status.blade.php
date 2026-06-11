@if ($data->status == 'Pending')
<span class="badge badge-info">
    Tertunda
</span>
@elseif ($data->status == 'Ordered')
<span class="badge badge-primary">
    Dipesan
</span>
@else
<span class="badge badge-success">
    Selesai
</span>
@endif