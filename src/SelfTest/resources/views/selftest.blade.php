<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>Self test page for {{config('app.name')}}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    </head>
    <body>
        <div class="container pt-5">
            <h3>Self test page for {{config('app.name')}}</h3>
            
            @foreach ($tests as $testElem)
            <br />
            <h4>{{ $testElem->name }}</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($testElem->actions as $action)
                    <tr id="{{ $action->id }}">
                        <td style="width:90%">{{ $action->label }}</td>
                        <td class="result-cell">Checking ...</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        </div>

        <script>
            var testData = {!! $testData !!};
            {!! file_get_contents(__DIR__.'/../../../vendor/jsantoso/laravel-services/src/SelfTest/public/js/selftest.js') !!}
        </script>
    </body>
</html>
