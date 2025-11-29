@extends('layouts.app')

@section('title', 'Request Tagihan Iuran')

@section('content')
<div class="container mx-auto px-4 py-6">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Request Tagihan Iuran</h2>

    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-3">Orang Tua</th>
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3">Bulan</th>
                    <th class="px-4 py-3">Total Tagihan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($requests as $req)
                    <tr class="border-b">
                        <td class="px-4 py-2 font-medium">
                            {{ $req->parent->userProfile->nama_lengkap ?? $req->parent->name }}
                        </td>

                        <td class="px-4 py-2">
                            <strong>{{ $req->student_count }} siswa</strong>
                            <ul class="text-xs px-2 list-disc">
                                @foreach($req->parent->children as $child)
                                    <li>{{ $child->userProfile->nama_lengkap ?? $child->name }}</li>
                                @endforeach
                            </ul>
                        </td>

                        <td class="px-4 py-2">
                            <strong>{{ $req->months }} Bulan</strong>
                            <ul class="text-xs px-2 list-disc">
                                @foreach($req->month_list as $month)
                                    <li>{{ $month }}</li>
                                @endforeach
                            </ul>
                        </td>

                        <td class="px-4 py-2">
                            Rp {{ number_format($req->total_tagihan, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-2 capitalize">
                            {{ $req->status }}
                        </td>

                        <td class="px-4 py-2 text-center">
                            @if($req->status === 'pending')
                                <button
                                    onclick="openDetail({{ $req->id }})"
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Lihat / Approve
                                </button>
                            @else
                                <span class="text-gray-400">Sudah Diproses</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                            Tidak ada request tagihan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $requests->links() }}
    </div>
</div>

{{-- MODAL PREVIEW --}}
<div id="requestModal"
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl">&times;</button>

        <h2 class="text-lg font-bold mb-4">Detail Request Iuran</h2>

        <div id="modal-content" class="space-y-2 text-sm text-gray-800">
            <!-- AJAX fill -->
        </div>

        <form id="approve-form" method="POST" action="{{ route('admin.iuran.approve', $req->id) }}">
            @csrf
            <button type="submit"
                class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
                Approve Request
            </button>
        </form>
    </div>
</div>

<script>
function openDetail(id) {
    fetch(`/admin/iuran/request-detail/${id}`)
        .then(res => res.json())
        .then(data => {

            let monthItems = data.month_list.map(m => `<li class="ml-4 list-disc">${m}</li>`).join('');

            document.getElementById('modal-content').innerHTML = `
                <p><strong>Orang Tua:</strong> ${data.parent}</p>
                <p><strong>Siswa:</strong> ${data.students}</p>
                
                <p><strong>Bulan Tagihan:</strong> ${data.months}</p>
                <ul class="mt-1">
                    ${monthItems}
                </ul>

                <p class="mt-3"><strong>Total Tagihan:</strong> Rp ${data.total_tagihan}</p>
                <p><strong>Diajukan Pada:</strong> ${data.created_at}</p>
            `;

            document.getElementById('approve-form').action = `/admin/iuran/approve/${id}`;
            document.getElementById('requestModal').classList.remove('hidden');
        });
}

function closeModal() {
    document.getElementById('requestModal').classList.add('hidden');
}
</script>

@endsection
