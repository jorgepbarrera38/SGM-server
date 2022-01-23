<table>
    <thead>
    <tr>
        <th><strong>FECHA</strong></th>
        <th><strong>REPUESTO - CONSUMIBLE</strong></th>
        <th><strong>CANTIDAD</strong></th>
        <th><strong>TIPO DE MANTENIMIENTO</strong></th>
        <th><strong>CLASIFICACIÓN DEL SERVICIO</strong></th>
        <th><strong>ELEMENTO</strong></th>
        <th><strong>PLACA</strong></th>
        <th><strong>SERIE</strong></th>
        <th><strong>MOTIVO</strong></th>
        <th><strong>CONCLUSIÓN</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach($replacementsUsed as $replacementUsed)
        <tr>
            <td>{{ $replacementUsed->replacementMaintenancable->date_of_realization_ending }}</td>
            <td>{{ $replacementUsed->replacement->name }}</td>
            <td>{{ $replacementUsed->quantity }}</td>
            <td>
                @if (!$replacementUsed->replacementMaintenancable->device_id)
                    Mantenimiento general
                
                @elseif(str_contains($replacementUsed->replacement_maintenancable_type, 'Other'))
                    Otro Mantenimiento
                @else
                    Mantenimiento preventivo
                @endif
            </td>
            <td>
                {{ $replacementUsed->replacementMaintenancable->typeOtherMaintenance ? $replacementUsed->replacementMaintenancable->typeOtherMaintenance->name : '' }}
            </td>
            <td>{{ $replacementUsed->replacementMaintenancable->device ? $replacementUsed->replacementMaintenancable->device->element->name : '' }}</td>
            <td>{{ $replacementUsed->replacementMaintenancable->device ? $replacementUsed->replacementMaintenancable->device->internal_code : '' }}</td>
            <td>{{ $replacementUsed->replacementMaintenancable->device ? $replacementUsed->replacementMaintenancable->device->serial_number : '' }}</td>
            <td>{{ $replacementUsed->replacementMaintenancable->reason }}</td>
            <td>{{ $replacementUsed->replacementMaintenancable->conclusion }}</td>

        </tr>
    @endforeach
    </tbody>
</table>