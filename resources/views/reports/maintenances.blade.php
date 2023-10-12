<table>
    <thead>
    <tr>
        <th><strong>GRUPO</strong></th>
        <th><strong>FECHA Y HORA INICIO DE REALIZACIÓN</strong></th>
        <th><strong>FECHA Y HORA FIN DE REALIZACIÓN</strong></th>
        <th><strong>UBICACIÓN</strong></th>
        <th><strong>DEPENDENCIA</strong></th>
        <th><strong>CLASIFICACIÓN DEL SERVICIO</strong></th>
        <th><strong>MOTIVO</strong></th>
        <th><strong>DESCRIPCIÓN DEL SOPORTE</strong></th>
        <th><strong>USUARIO</strong></th>
        <th><strong>REPUESTOS O CONSUMIBLES USADOS</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($maintenances as $maintenance)
        <tr>
            <td>{{ $maintenance->group->name }}</td>
            <td>{{ $maintenance->date_of_realization }}</td>
            <td>{{ $maintenance->date_of_realization_ending }}</td>
            <td>{{ $maintenance->location->name }}</td>
            <td>{{ $maintenance->dependence->name }}</td>
            <td>{{ $maintenance->typeOtherMaintenance->name }}</td>
            <td>{{ $maintenance->reason }}</td>
            <td>{{ $maintenance->support_description }}</td>
            <td>{{ $maintenance->user->names }}</td>
            <td>
                @foreach ($maintenance->replacements as $replacement )
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