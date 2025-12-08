<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            color: #1a202c;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    {{ config('app.name') }}
</body>
</html>
