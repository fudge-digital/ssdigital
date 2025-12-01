<div class="bg-white mt-6">
    <h2 class="text-xl font-bold">Dokumen Siswa</h2>
    <p class="text-xs text-gray-400 mb-2"><span class="text-red-500 mr-2">(*)</span> Kartu Keluarga dan Akta Lahir wajib untuk di upload</p>
    
    @php
        $dokumen = $student->profile?->documents ?? null;
    @endphp

    <table class="w-full text-left text-sm">
        @foreach (['kk' => 'Kartu Keluarga', 'akta' => 'Akta Lahir', 'ijazah' => 'Ijazah', 'nisn' => 'NISN'] as $key => $label)
            @php
                $required = in_array($key, ['kk','akta']); // dokumen wajib
                $existingDoc = $student->profile?->documents?->where('type', $key)->first();
            @endphp
            <tr>
                <th class="py-2 px-4 border-b w-1/3 font-semibold">
                    @if($required)
                        <span class="text-red-600 font-bold mr-2">(*)</span>
                    @endif
                    {{ $label }}
                </th>

                <td class="py-2 px-4 border-b" id="{{ $key }}-wrapper">
                    {{-- Jika belum ada dokumen --}}
                    @if(!$existingDoc)
                        <span class="text-red-500 text-xs">Belum ada dokumen</span>

                        @if(auth()->user()->hasRole(['admin','orang_tua']))
                            <form action="{{ route('document.upload', [$student->profile->id, $key]) }}"
                                method="POST" enctype="multipart/form-data"
                                class="inline-block ml-3">
                                @csrf
                                <input type="file" name="file"
                                    class="text-sm"
                                    onchange="this.form.submit()">
                            </form>
                        @endif
                    @else
                        {{-- Jika dokumen sudah ada --}}
                        <div class="flex flex-row justify-between items-center">
                            <button onclick="openDocModal('{{ asset('storage/' . $existingDoc->file_path) }}')"
                                    class="text-blue-600 text-sm">
                                <i class="fa-solid fa-eye mr-1"></i> Lihat dokumen
                            </button>

                            @if(auth()->user()->hasRole(['admin','orang_tua']))
                                {{-- Tombol Hapus (AJAX) --}}
                                <button class="bg-red-600 text-white p-1 rounded text-xs font-medium ml-4 delete-document"
                                        data-id="{{ $existingDoc->id }}"
                                        data-type="{{ $key }}">
                                    Hapus
                                </button>
                            @endif
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Dokumen Lain --}}
    <div class="mt-6">
        <h3 class="text-lg font-semibold">Dokumen Lain</h3>
        <p class="text-xs text-gray-400">Jika ada dokumen pendukung (surat pindah, surat keterangan, dll)</p>
        @php
            $dokumenLain = $student->profile?->documents?->where('type', 'lain');
        @endphp

        <table class="w-full text-left text-sm border rounded mt-3">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-3 border">Judul</th>
                    <th class="py-2 px-3 border">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($dokumenLain ?? [] as $doc)
                <tr>
                    <td class="py-2 px-3 border">{{ $doc->title }}</td>
                    <td class="py-2 px-3 border">
                        <button onclick="openDocModal('{{ asset('storage/' . $doc->file_path) }}')"
                                class="text-blue-600 underline text-sm">
                            Lihat
                        </button>

                        @if(auth()->user()->hasRole(['admin','orang_tua']))
                            <form action="{{ route('document.delete', $doc->id) }}"
                                method="POST" class="inline-block ml-2"
                                onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 underline text-sm">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center py-3 text-red-600 text-sm border">
                        Belum ada dokumen lain
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Upload dokumen lain --}}
    @if(auth()->user()->hasRole(['admin','orang_tua']))
        <div class="mt-4">
            <form action="{{ route('document.upload', [$student->profile->id, 'lain']) }}"
                method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf

                <input type="text" name="title" placeholder="Judul dokumen"
                    class="border rounded px-2 py-1 text-sm w-1/3" required>

                <input type="file" name="file"
                    class="text-sm border rounded px-2 py-1"
                    onchange="this.form.submit()" required>
            </form>
        </div>
    @endif

</div>

{{-- Modal preview --}}
@include('siswa.partials.documents-modal')

<script>
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("delete-document")) {
            let id = e.target.dataset.id;
            let type = e.target.dataset.type;

            if (!confirm("Yakin ingin menghapus dokumen ini?")) return;

            fetch(`/document/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {

                document.getElementById(`${type}-wrapper`).innerHTML = `
                    <span class="text-red-500 text-xs">Belum ada dokumen</span>

                    <form action="/siswa/{{ $student->profile->id }}/document/${type}"
                        method="POST" enctype="multipart/form-data"
                        class="inline-block ml-3">
                        @csrf
                        <input type="file" name="file"
                            class="text-sm"
                            onchange="this.form.submit()">
                    </form>
                `;
            })
            .catch(err => console.error(err));
        }
    });
</script>
