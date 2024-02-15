<p>
    Hello <b>{{ $user->name}}</b>,
</p>
<p>
    User <b>{{ $author->name }}</b>, has shared the below files with you.
</p>

<ul>
    @foreach($shared_files as $file)
        <li>
            <span>{{$file->name}}</span>
        </li>
    @endforeach
</ul>