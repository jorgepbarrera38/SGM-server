<table>
    <thead>
    <tr>
        <th><strong>FECHA DE REALIZACIÓN</strong></th>
        <th><strong>GRUPO</strong></th>
        <th><strong>ELEMENTO</strong></th>
        <th><strong>PLACA</strong></th>
        <th><strong>SERIAL</strong></th>
        <th><strong>UBICACIÓN</strong></th>
        <th><strong>DEPENDENCIA</strong></th>
        <th><strong>USUARIO</strong></th>
        <th><strong>REPUESTOS O CONSUMIBLES USADOS</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($preventiveMaintenances as $preventiveMaintenance)
        <tr>
            <td>{{ $preventiveMaintenance->date_of_realization }}</td>
            <td>{{ $preventiveMaintenance->device->group->name }}</td> 
            <td>{{ $preventiveMaintenance->device->element->name }}</td> 
            <td>{{ $preventiveMaintenance->device->internal_code }}</td>  
            <td>{{ $preventiveMaintenance->device->serial_number }}</td>
            <td>{{ $preventiveMaintenance->location->name }}</td>  
            <td>{{ $preventiveMaintenance->dependence->name }}</td>  
            <td>{{ $preventiveMaintenance->user->names }}</td>
            <td>
                @foreach ($preventiveMaintenance->replacementMaintenancables as $replacement )
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