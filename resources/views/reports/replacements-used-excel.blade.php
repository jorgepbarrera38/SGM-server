<table>
    <thead>
    <tr>
        <th><strong>TIPO DE MANTENIMIENTO</strong></th>
        <th><strong>FECHA</strong></th>
        <th><strong>REPUESTO - CONSUMIBLE</strong></th>
        <th><strong>CANTIDAD</strong></th>
        <th><strong>ELEMENTO</strong></th>
        <th><strong>PLACA</strong></th>
        <th><strong>SERIE</strong></th>
        <th><strong>MOTIVO</strong></th>
        <th><strong>DESCRIPCIÓN DEL SOPORTE</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($replacementsUsed as $replacementUsed)
        <tr>
            <td>
                @if($replacementUsed->type == 'preventive_maintenance')
                    Mantenimiento preventivo
                @endif
                @if($replacementUsed->type == 'other_maintenance')
                    Otro mantenimiento
                @endif
                @if($replacementUsed->type == 'general_maintenance')
                    Mantenimiento general
                @endif
            </td>
            <td>{{ $replacementUsed->maintenance_date_of_realization }}</td>
            <td>{{ $replacementUsed->replacement_name }}</td>
            <td>{{ $replacementUsed->replacement_quantity }}</td>
            <td>{{ $replacementUsed->element_name }}</td>
            <td>{{ $replacementUsed->device_internal_code }}</td>
            <td>{{ $replacementUsed->device_serial_number }}</td>
            <td>{{ $replacementUsed->preventive_maintenance_reason }}</td>
            <td>{{ $replacementUsed->preventive_maintenance_support_description }}</td>
        </tr>
    @endforeach
    </tbody>
</table>