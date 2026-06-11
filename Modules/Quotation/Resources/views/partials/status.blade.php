@if ($data->status == 'Pending')
<span class="badge badge-info">
    Tertunda
</span>
@else
<span class="badge badge-success">
    Terkirim
</span>
@endif