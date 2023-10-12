<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen</title>
    <link rel="stylesheet" href="https://www.cufimer.com/sgm/styles.css">
    <style>
        
    </style>
</head>
<body>
    <div id="watermark">
        @if($company)
            <img src="{{ $company->watermark ? env('APP_URL') . $company->watermark : '' }}" height="100%" width="100%" />
        @endif
    </div>

    <table border="1" style="table-layout: fixed;width: 100%;">
            <tr>
                <th style="padding: 0px" rowspan="3" colspan="4">
                    @if($company)
                        <img src="{{ $company->logo ? env('APP_URL') . $company->logo : '' }}" width="100">
                    @endif
                </th>
                <th rowspan="2" colspan="8">TECNOLOGÍA INFORMÁTICA</th>
                <th class="align-justify" colspan="4">Código: A-IS-FO-01</th>
            </tr>
            <tr>
                
                <th class="align-justify" colspan="4">Versión 1.0</th>
            </tr>
            <tr>
                <th colspan="8">FORMATO DE REPORTE DE SOPORTE TÉCNICO</th>
                <th class="align-justify" colspan="4">Fecha de aprobación: <br>01/12/2019</th>
            </tr>

            <tr>
                <th colspan="16">
                    <span style="color:white">space</span>
                </th>
            </tr>
        
            <tr>
                <th colspan="4" class="title align-justify">HOJA DE VIDA</th>
                <th colspan="4" class="align-justify">{{ $device->resume_number }}</th>
                <th colspan="4" class="title align-justify">GRUPO</th>
                <th colspan="4" class="align-justify">{{ $device->group->name }}</th>
            </tr>
            <tr>
                <th colspan="4" class="title align-justify">ELEMENTO</th>
                <th colspan="4" class="align-justify">{{ $device->element->name }}</th>
                <th colspan="4" class="title align-justify">UBICACIÓN</th>
                <th colspan="4" class="align-justify">{{ $device->location->name }}</th>
            </tr>
            <tr>
                <th colspan="4" class="title align-justify">DEPENDENCIA</th>
                <th colspan="4" class="align-justify">{{ $device->dependence->name }}</th>
                <th colspan="4" class="title align-justify">RESPONSABLE</th>
                <th colspan="4" class="align-justify">{{ $device->responsible->fullnames }}</th>
            </tr>
            
            <tr>
                <th colspan="4" class="title align-justify">PLACA</th>
                <th colspan="4" class="align-justify">{{ $device->internal_code }}</th>
                <th colspan="4" class="title align-justify">SERIAL</th>
                <th colspan="4" class="align-justify">{{ $device->serial_number }}</th>
            </tr>

            <tr>
                <th colspan="4" class="title align-justify">MARCA</th>
                <th colspan="4" class="align-justify">{{ $device->brand->name }}</th>
                <th colspan="4" class="title align-justify">MODELO</th>
                <th colspan="4" class="align-justify">{{ $device->model }}</th>
            </tr>
            <tr>
                <th colspan="8" class="title align-justify">FECHA Y HORA INICIO DE REALIZACIÓN</th>
                <th colspan="8" class="align-justify">{{ $maintenance->date_of_realization }}</th>
            </tr>
            <tr>
                <th colspan="8" class="title align-justify">FECHA Y HORA FIN DE REALIZACIÓN</th>
                <th colspan="8" class="align-justify">{{ $maintenance->date_of_realization_ending }}</th>
            </tr>

            <tr>
                <th colspan="16">
                    <span style="color:white">space</span>
                </th>
            </tr>

            <tr>
                <th colspan="16" class="title">
                    CLASIFICACIÓN DEL SERVICIO
                </th>
            </tr>
            <tr>
                <th colspan="16" style="text-align: justify;">
                    {{ $maintenance->typeOtherMaintenance ? $maintenance->typeOtherMaintenance->name : 'Mantenimiento preventivo' }}
                </th>
            </tr>
            <tr>
                <th colspan="16" class="title">MOTIVO DEL SERVICIO</th>
            </tr>
            <tr>
                <th colspan="16" style="text-align: justify;">
                    {{ $maintenance->reason }}
                </th>
            </tr>
            <tr>
                <th colspan="16" class="title">DESCRIPCIÓN DEL SOPORTE TÉCNICO</th>
            </tr>
            <tr>
                <th colspan="16" style="text-align: justify">{{ $maintenance->support_description }}</th>
            </tr>


            <tr>
                <th colspan="16" class="title">ANEXOS FOTOGRÁFICOS</th>
            </tr>
        
            @foreach (array_chunk($maintenance->photographicAnnexes->toArray(), 2) as $photographicAnnexeGrouped)
                <tr>
                    @foreach($photographicAnnexeGrouped as $photographicAnnexe)
                        <th colspan="8" class="title">{{ $photographicAnnexe['description'] }}</th>
                    @endforeach
                    @if($loop->last && count($photographicAnnexeGrouped) == 1)
                        <th colspan="8" class="title"></th>
                    @endif
                </tr>
                <tr>
                    @foreach($photographicAnnexeGrouped as $photographicAnnexe)
                        <th colspan="8">
                            <img src="{{ env('APP_URL') . $photographicAnnexe['image'] }}" width="170">
                        </th>                
                    @endforeach
                    @if($loop->last && count($photographicAnnexeGrouped) == 1)
                        <th colspan="8"></th>
                    @endif
                </tr>
            @endforeach
                    
            <tr>
                <th colspan="16" class="title">GASTOS GENERADOS</th>
            </tr>
            <tr>
                <th colspan="8" class="title">DESCRIPCIÓN</th>
                <th colspan="8" class="title">CANTIDAD</th>
            </tr>
            @foreach ($maintenance->replacements as $replacement)
            <tr>
                <th colspan="8" style="text-align: justify;">{{ $replacement->replacement['name'] }}</th>
                <th colspan="8">{{ $replacement['quantity'] }}</th>
            </tr>
            @endforeach


            <tr>
                <th colspan="16" class="title">LISTAS DE CHEQUEO</th>
            </tr>

            @if($maintenance->checklists)
                @foreach($maintenance->checklists as $checklist)
                    <tr>
                        <th colspan="16" class="title">{{ $checklist->name }}</th>
                    </tr>
                    <tr>
                        <th colspan="13" class="title">DESCRIPCIÓN</th>
                        <th colspan="1" class="title">C</th>
                        <th colspan="1" class="title">NC</th>
                        <th colspan="1" class="title">NA</th>
                    </tr>
                    @foreach ($checklist->checklistGroups as $checklistGroup)
                    <tr>
                        <th colspan="16" style="text-align: justify;">{{ $checklistGroup->name }}</th>
                    </tr>
                    
                        @foreach ($checklistGroup->checklistItems as $checklistItem)
                            <tr>
                                <th colspan="13" style="text-align: justify;">{{ $checklistItem->description }}</th>
                                <th colspan="1">{{ $checklistItem->response == 'C' ? 'X' : '' }}</th>
                                <th colspan="1">{{ $checklistItem->response == 'NC' ? 'X' : '' }}</th>
                                <th colspan="1">{{ $checklistItem->response == 'NA' ? 'X' : '' }}</th>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endif


            <tr>
                <th colspan="8" class="title">QUIÉN EJECUTA</th>
                <th colspan="8" class="title">QUIÉN VERIFICA</th>
            </tr>
            <tr>
                <th colspan="4" class="sub-title ">FIRMA</th>
                <th colspan="4">
                    <img src="{{ auth()->user()->signature ? env('APP_URL') . auth()->user()->signature : '' }}" width="160" style="max-width:100%">
                </th>
                <th colspan="4" class="sub-title ">FIRMA</th>
                <th colspan="4">
                        <img src="{{ $maintenance->signature }}" width="200" style="max-width:100%">
                </th>
            </tr>
            <tr>
                <th colspan="4" class="sub-title">NOMBRE</th>
                <th colspan="4">{{ auth()->user()->names }}</th>
                <th colspan="4" class="sub-title ">NOMBRE</th>
                <th colspan="4">{{ ucwords(strtolower($maintenance->official_names)) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="sub-title ">Cargo</th>
                <th colspan="4">{{ auth()->user()->official_position }}</th>
                <th colspan="4" class="sub-title ">Cargo</th>
                <th colspan="4">{{ ucwords(strtolower($maintenance->official_position)) }}</th>
            </tr>
        </table>
    </body>
</html>