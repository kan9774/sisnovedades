<span wire:poll.30s="$refresh">
    @if ($this->pendientes > 0)
        <span class="badge badge-danger ml-1">{{ $this->pendientes }}</span>
    @endif
</span>