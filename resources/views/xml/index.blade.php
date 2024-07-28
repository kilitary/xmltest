<!DOCTYPE html>
<html>
<head>
    <title>upload</title>
</head>
<body>
@if(session('success'))
    <span style="color:green">{{ session('success') }}</span>
@endif
@if(session('error'))
    <span style="color:red">{{ session('error') }}</span>
@endif
<form action="/xml/upload" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">загрузка</button>
</form>

<table>
    <tr>
        <th>имя</th>
        <th>на диске</th>
        <th>добавлен</th>
        <th>json</th>
    </tr>
    @foreach($xmls as $xml)
        <tr>
            <td>{{$xml->file_name}}</td>
            <td>{{$xml->file_id}}</td>
            <td>{{$xml->created_at}}</td>
            <td><a href="/xml/json/{{$xml->id}}">скачать</a></td>
        </tr>
    @endforeach
</table>
</body>
</html>
