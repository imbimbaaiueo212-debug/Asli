@if (session('failures') && count(session('failures'))>0)
  <div class="alert alert-warning">
    <strong>Baris gagal diimpor:</strong>
    <ul class="mb-0">
      @foreach (session('failures') as $f)
        <li>
          Baris {{ $f->row() }} — {{ implode('; ', $f->errors()) }}
          @php $vals = $f->values(); @endphp
          <div class="small text-muted">Data: {{ json_encode($vals) }}</div>
        </li>
      @endforeach
    </ul>
  </div>
@endif