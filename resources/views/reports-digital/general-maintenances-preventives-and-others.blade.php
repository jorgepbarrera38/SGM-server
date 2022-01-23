<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <table border="1" width="100%">
        <tr>
            <th style="padding: 0px" rowspan="3">
                <img src="{{ $companyLogo }}" width="80">
            </th>
            <th rowspan="2">TECNOLOGÍA INFORMÁTICA</th>
            <th class="align-justify">Código: A-IS-FO-01</th>
        </tr>
        <tr>
            
            <th class="align-justify">Versión 1.0</th>
        </tr>
        <tr>
            <th>FORMATO DE REPORTE DE SOPORTE TÉCNICO</th>
            <th class="align-justify">Fecha de aprobación: <br>01/12/2019</th>
        </tr>
    </table>
    <br>
    @if ($device)
        <table border="1" width="100%">
            <tr>
                <th class="title align-justify">HOJA DE VIDA</th>
                <th class="align-justify">{{ $device->resume_number }}</th>
                <th class="title align-justify">FECHA</th>
                <th class="align-justify">{{ Carbon\Carbon::createFromDate($date_of_realization_ending) }}</th>
            </tr>
            <tr>
                <th class="title align-justify">EQUIPO Y/O ELEMENTO</th>
                <th class="align-justify">{{ $device->element->name }}</th>
                <th class="title align-justify">MARCA</th>
                <th class="align-justify">{{ $device->brand->name }}</th>
            </tr>
            <tr>
                <th class="title align-justify">MODELO</th>
                <th class="align-justify">{{ $device->model }}</th>
                <th class="title align-justify">PLACA</th>
                <th class="align-justify">{{ $device->internal_code }}</th>
            </tr>
            <tr>
                <th class="title align-justify">SERIE</th>
                <th colspan="3" class="align-justify">{{ $device->serial_number }}</th>
            </tr>
            <tr>
                <th class="title align-justify">UBICACIÓN</th>
                <th class="align-justify">{{ $device->location->name }}</th>
                <th class="title align-justify">DEPENDENCIA</th>
                <th class="align-justify">{{ $device->dependence->name }}</th>
            </tr>
        </table>
    @else 
        <table border="1" width="100%">
            <tr>
                <th class="title align-justify">FECHA</th>
                <th colspan="3" class="align-justify">{{ Carbon\Carbon::createFromDate($date_of_realization_ending) }}</th>
            </tr>
            <tr>
                <th class="title align-justify">UBICACIÓN</th>
                <th class="align-justify">{{ $location ? $location->name : '' }}</th>
                <th class="title align-justify">DEPENDENCIA</th>
                <th class="align-justify">{{ $dependence ? $dependence->name : '' }}</th>
            </tr>
        </table>
    @endif
    <br>
    <table class="table" border="1"  width="100%">
        <tr>
            <th colspan="4" class="title">
                CLASIFICACIÓN DEL SERVICIO
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: justify;">
                {{ $typeMaintenance ? $typeMaintenance->name : 'Mantenimiento preventivo' }}
            </th>
        </tr>
        <tr>
            <th colspan="4" class="title">MOTIVO DEL SERVICIO</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: justify;">
                @if ($reason)
                    {{ $reason }}
                @else
                    Mantenimiento preventivo programado
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="4" class="title">DESCRIPCIÓN DEL SOPORTE TÉCNICO</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: justify">{{ $conclusion }}</th>
        </tr>


        <tr>
            <th colspan="4" class="title">ANEXOS FOTOGRÁFICOS</th>
        </tr>
       
        @foreach ($photographicAnnexes as $photographicAnnexeGrouped)
            <tr>
                @foreach($photographicAnnexeGrouped as $photographicAnnexe)
                    <th colspan="2" class="title">{{ $photographicAnnexe['description'] }}</th>
                @endforeach
                @if($loop->last && count($photographicAnnexeGrouped) == 1)
                    <th colspan="2" class="title"></th>
                @endif
            </tr>
            <tr>
                @foreach($photographicAnnexeGrouped as $photographicAnnexe)
                    <th colspan="2">
                        <img src="{{ $photographicAnnexe['image'] }}" width="200">
                    </th>                
                @endforeach
                @if($loop->last && count($photographicAnnexeGrouped) == 1)
                    <th colspan="2"></th>
                @endif
            </tr>
        @endforeach
                
        <tr>
            <th colspan="4" class="title">GASTOS GENERADOS</th>
        </tr>
        <tr>
            <th colspan="2" class="title">DESCRIPCIÓN</th>
            <th colspan="2" class="title">CANTIDAD</th>
        </tr>
        @foreach ($replacementsFromReport as $replacement)
        <tr>
            <th colspan="2" style="text-align: justify;">{{ $replacement['name'] }}</th>
            <th colspan="2">{{ $replacement['quantity'] }}</th>
        </tr>
        @endforeach
        <tr>
            <th colspan="2" class="title">QUIÉN EJECUTA</th>
            <th colspan="2" class="title">QUIÉN VERIFICA</th>
        </tr>
        <tr>
            <th class="sub-title ">FIRMA</th>
            <th>
                <img src="{{ auth()->user()->signature }}" width="160">
            </th>
            <th class="sub-title ">FIRMA</th>
            <th>
                    <img src="{{ $signature }}" width="200">
            </th>
        </tr>
        <tr>
            <th class="sub-title">NOMBRE</th>
            <th>{{ auth()->user()->names }}</th>
            <th class="sub-title ">NOMBRE</th>
            <th>{{ ucwords(strtolower($official_names)) }}</th>
        </tr>
        <tr>
            <th class="sub-title ">Cargo</th>
            <th>{{ auth()->user()->official_position }}</th>
            <th class="sub-title ">Cargo</th>
            <th>{{ ucwords(strtolower($official_position)) }}</th>
        </tr>
    </table>
</body>
</html>