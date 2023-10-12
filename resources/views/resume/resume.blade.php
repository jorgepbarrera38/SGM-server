<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen</title>
    <link rel="stylesheet" href="https://www.cufimer.com/sgm/styles.css">
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div id="watermark">
        @if($company)
            <img src="{{ $company->watermark ? env('APP_URL') . $company->watermark : '' }}" height="100%" width="100%" />
        @endif        
    </div>
    
        <table border="1" width="100%">
            <tr>
                <th style="padding: 0px" rowspan="3" colspan="1">
                    @if($company)
                        <img src="{{ $company->logo ? env('APP_URL') . $company->logo : '' }}" width="100">
                    @endif
                </th>
                <th rowspan="2" colspan="2">TECNOLOGÍA INFORMÁTICA</th>
                <th class="align-justify" colspan="1">Código: A-IS-FO-01</th>
            </tr>
            <tr>
                <th class="align-justify" colspan="1">Versión 1.0</th>
            </tr>
            <tr>
                <th colspan="2">FORMATO DE ESPECIFICACIÓNES TÉCNICAS</th>
                <th class="align-justify" colspan="1">Fecha de aprobación: <br>01/12/2019</th>
            </tr>

            <tr>
                <th colspan="4">
                    <span style="color:white">space</span>
                </th>
            </tr>

            <tr>
                <th colspan="4" class="title align-justify">
                    <center>INFORMACIÓN DEL DISPOSITIVO</center>     
                </th>
            </tr>

            <tr>
                <th class="title align-justify">HOJA DE VIDA</th>
                <th class="align-justify">{{ $device->resume_number }}</th>
                <th class="title align-justify">GRUPO</th>
                <th class="align-justify">{{ $device->group->name }}</th>
            </tr>

            <tr>
                <th class="title align-justify">ELEMENTO</th>
                <th class="align-justify">{{ $device->element->name }}</th>
                <th class="title align-justify">UBICACIÓN</th>
                <th class="align-justify">{{ $device->location->name }}</th>
            </tr>

            <tr>
                <th class="title align-justify">DEPENDENCIA</th>
                <th class="align-justify">{{ $device->dependence->name }}</th>
                <th class="title align-justify">RESPONSABLE</th>
                <th class="align-justify">{{ $device->responsible->fullnames }}</th>
            </tr>

            <tr>
                <th class="title align-justify">PLACA</th>
                <th class="align-justify">{{ $device->internal_code }}</th>
                <th class="title align-justify">SERIAL</th>
                <th class="align-justify">{{ $device->serial_number }}</th>
            </tr>

            <tr>
                <th class="title align-justify">MARCA</th>
                <th class="align-justify">{{ $device->brand->name }}</th>
                <th class="title align-justify">MODELO</th>
                <th class="align-justify">{{ $device->model }}</th>
            </tr>

            <tr>
                <th class="title align-justify">INICIO DE OPERACIÓN</th>
                <th class="align-justify">{{ $device->start_operation }}</th>
                <th class="title align-justify">FECHA FIN GARANTÍA</th>
                <th class="align-justify">{{ $device->date_end_guarantee }}</th>
            </tr>

            <tr>
                <th class="title align-justify">VIDA ÚTIL</th>
                <th colspan="3" class="align-justify">{{ $device->useful_life }} años</th>
            </tr>

            <tr>
                <th class="title align-justify">DESCRIPCIÓN</th>
                <th colspan="3" class="align-justify">{{ $device->description }}</th>
            </tr>

            <tr>
                <th class="title align-justify">OBSERVACIONES</th>
                <th colspan="3" class="align-justify">{{ $device->observations }}</th>
            </tr>
            <tr>
                <th class="title align-justify" colspan="4">
                    <center>
                        REGISTRO FOTOGRÁFICO
                    </center> 
                </th>
            </tr>
            @foreach (array_chunk($device->photographicRecords->toArray(), 2) as $photographicRecordGrouped)
               
                <tr>
                    @foreach($photographicRecordGrouped as $photographicRecord)
                        <th colspan="2">
                            <img src="{{ env('APP_URL') . $photographicRecord['photo'] }}" width="170">
                        </th>                
                    @endforeach
                    @if($loop->last && count($photographicRecordGrouped) == 1)
                        <th colspan="2"></th>
                    @endif
                </tr>
            @endforeach
        </table>

</body>
</html>