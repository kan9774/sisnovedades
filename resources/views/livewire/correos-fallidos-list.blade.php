<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Correos Fallidos</h3>
        </div>
        <div class="card-body">
            @if ($correosFallidos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Destinatario</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($correosFallidos as $fallido)
                                <tr>
                                    <td>{{ $fallido->email }}</td>
                                    <td>{{ $fallido->motivo }}</td>
                                    <td>{{ $fallido->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    No hay correos fallidos registrados para esta guardia.
                </div>
            @endif
        </div>
    </div>
</div>