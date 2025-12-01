@if ($errors->any())
    <div class="mb-4 p-4 rounded-lg bg-red-100 border border-red-300 text-red-800">
        <ul class="list-disc ps-4 space-y-1">
            @foreach ($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif