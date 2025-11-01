@props(['url'])
<tr>
<td class="header">
    <!--<a href="{{ $url }}" style="display: inline-block;">
        <img src="{{ asset('images/kaila.png')}}" style="width: 90px; height:100px" class="logo" alt="Kaila Logo">
    </a>
    <br/>-->

    <a href="{{ $url }}" style="display: inline-block;">
        {{ $slot }}
    </a>

</td>
</tr>
