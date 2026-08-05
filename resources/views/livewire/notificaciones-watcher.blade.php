{{-- Componente invisible: solo dispara polling periódico para detectar novedades nuevas --}}
<div wire:poll.15s="poll" style="display:none"></div>