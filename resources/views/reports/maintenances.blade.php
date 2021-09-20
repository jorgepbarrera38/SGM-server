<table>
    <thead>
    <tr>
        <th><strong>FECHA DE REALIZACIÓN</strong></th>
        <th><strong>UBICACIÓN</strong></th>
        <th><strong>DEPENDENCIA</strong></th>
        <th><strong>CLASIFICACIÓN DEL SERVICIO</strong></th>
        <th><strong>MOTIVO</strong></th>
        <th><strong>CONCLUSIÓN</strong></th>
        <th><strong>USUARIO</strong></th>
        <th><strong>REPUESTOS O CONSUMIBLES USADOS</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($maintenances as $maintenance)
        <tr>
            <td>{{ $maintenance->date_of_realization }}</td>
            <td>{{ $maintenance->location->name }}</td>
            <td>{{ $maintenance->dependence->name }}</td>
            <td>{{ $maintenance->typeOtherMaintenance->name }}</td>
            <td>{{ $maintenance->reason }}</td>
            <td>{{ $maintenance->conclusion }}</td>
            <td>{{ $maintenance->user->names }}</td>
            <td>
                @foreach ($maintenance->replacementMaintenancables as $replacement )
                    {{ $replacement->replacement->name . ' - cantidad:' . $replacement->quantity }} 
                    @if (!$loop->last)
                        <br>
                    @endif
                @endforeach
            </td>
        </tr>
    @endforeach
    </tbody>
</table>