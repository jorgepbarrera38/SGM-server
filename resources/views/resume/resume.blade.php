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
                <th>ESPECIFICACIÓNES TÉCNICAS</th>
                <th class="align-justify">Fecha de aprobación: <br>01/12/2019</th>
            </tr>
        </table>
        <br>
        <table border="1" width="100%">
            <tr>
                <th class="title align-justify">HOJA DE VIDA</th>
                <th colspan="3" class="align-justify">{{ $device->resume_number }}</th>
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
                <th class="title align-justify">UBICACIÓN</th>
                <th class="align-justify">{{ $device->location->name }}</th>
                <th class="title align-justify">DEPENDENCIA</th>
                <th class="align-justify">{{ $device->dependence->name }}</th>
            </tr>
            <tr>
                <th class="title align-justify">DESCRIPCIÓN</th>
                <th colspan="3" class="align-justify">{{ $device->description }}</th>
            </tr>
        </table>
    </body>
</html>