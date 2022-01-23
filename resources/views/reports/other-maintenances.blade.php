<table>
    <thead>
    <tr>
        <th><strong>FECHA Y HORA INICIO DE REALIZACIÓN</strong></th>
        <th><strong>FECHA Y HORA FIN DE REALIZACIÓN</strong></th>
        <th><strong>GRUPO</strong></th>
        <th><strong>ELEMENTO</strong></th>
        <th><strong>PLACA</strong></th>
        <th><strong>SERIAL</strong></th>
        <th><strong>UBICACIÓN</strong></th>
        <th><strong>DEPENDENCIA</strong></th>
        <th><strong>CLASIFICACIÓN DEL SERVICIO</strong></th>
        <th><strong>MOTIVO</strong></th>
        <th><strong>CONCLUSIÓN</strong></th>
        <th><strong>MODO DE FALLA</strong></th>
        <th><strong>USUARIO</strong></th>
        <th><strong>REPUESTOS O CONSUMIBLES USADOS</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($otherMaintenances as $otherMaintenance)
        <tr>
            <td>{{ $otherMaintenance->date_of_realization }}</td>
            <td>{{ $otherMaintenance->date_of_realization_ending }}</td>
            <td>{{ $otherMaintenance->device->group->name }}</td> 
            <td>{{ $otherMaintenance->device->element->name }}</td> 
            <td>{{ $otherMaintenance->device->internal_code }}</td>  
            <td>{{ $otherMaintenance->device->serial_number }}</td>
            <td>{{ $otherMaintenance->location->name }}</td>  
            <td>{{ $otherMaintenance->dependence->name }}</td>  
            <td>{{ $otherMaintenance->typeOtherMaintenance->name }}</td>
            <td>{{ $otherMaintenance->reason }}</td>
            <td>{{ $otherMaintenance->conclusion }}</td>
            <td>{{ $otherMaintenance->failureMode ? $otherMaintenance->failureMode->name : '' }}</td>
            <td>{{ $otherMaintenance->user->names }}</td>
            <td>
                @foreach ($otherMaintenance->replacementMaintenancables as $replacement )
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