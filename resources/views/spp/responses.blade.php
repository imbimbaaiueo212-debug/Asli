<table class="table table-bordered">
    <thead>
        <tr>
            @foreach ($responses->first() ?? [] as $key => $value)
                <th>{{ $key }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($responses as $response)
            <tr>
                @foreach ($response as $value)
                    <td>{{ $value }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
