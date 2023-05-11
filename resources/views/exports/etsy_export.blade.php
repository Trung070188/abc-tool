<table class="table table-bordered" style="width: 100%">
    <thead>
    <tr>
        @foreach($data as $key =>$item)
            <th>img {{$key+1}}</th>
        @endforeach
    </tr>
    </thead>

    <tbody>
    <tr>
        @foreach($data as $key => $item)
            <td style="border: 1px solid black">{{$item['link_img']}}</td>
        @endforeach
    </tr>
    </tbody>
</table>
