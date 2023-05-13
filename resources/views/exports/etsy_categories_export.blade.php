<table class="table table-bordered" style="width: 100%">
    <thead>
    <tr>
        <th>Link shop</th>
        <th>title</th>
        @for($i = 1; $i <= count($data[0]['img']); $i++)
            <th>img {{$i}}</th>
        @endfor
    </tr>
    </thead>

    <tbody>
    @foreach($data as $key => $item)
        <tr>
            <td>{{$item['link_shop']}}</td>
            <td>{{$item['title']}}</td>
            @foreach($item['img'] as $abc)
                <td style="border: 1px solid black">{{$abc['link_img']}}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
