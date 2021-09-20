<table>
    <thead>
    <tr>
        <th><strong>DESCRIPCIÓN</strong></th>
        <th><strong>ELEMENTO</strong></th>
        <th><strong>H.V</strong></th>
        <th><strong>PLACA</strong></th>
        <th><strong>SERIE</strong></th>
        <th><strong>UBICACIÓN</strong></th>
        <th><strong>DEPENDENCIA</strong></th>
        <th><strong>FECHA PROGRAMADA</strong></th>
        <th><strong>ESTADO</strong></th>
        <th><strong>OBSERVACIÓNES</strong></th>
    </tr>
    </thead>
    <tbody>
        @foreach ($scheduleMaintenance->scheduleMaintenanceDetails as $scheduleMaintenanceDetail)
        <tr>
            <td>{{ $scheduleMaintenanceDetail->device->description }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->element->name }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->id }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->internal_code }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->serial_number }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->location->name }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->dependence->name }}</td>
            <td>{{ getMonth($scheduleMaintenanceDetail->date->month) }}</td>
            <td>{{ $scheduleMaintenanceDetail->made ? 'Realizado' : 'Pendiente' }}</td>
            <td>{{ $scheduleMaintenanceDetail->device->description_support_preventive_maintenance }}</td>
        </tr>
        @endforeach
    </tbody>
</table>