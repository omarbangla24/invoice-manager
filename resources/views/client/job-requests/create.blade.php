@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <h1 class="h1">New Job Request</h1>
        <p class="muted">Select one or more jobs and submit for review.</p>
    </div>
</div>
@if($errors->any())
    <div class="alert err">{{ $errors->first() }}</div>
@endif
<div class="jr-create-layout">
    <section class="card jr-create-jobs">
        <h2>Select Jobs</h2>
        <form class="filterbar" method="get" style="margin-bottom:16px">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="Search jobs...">
            <select class="select" name="category">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select class="select" name="per_page">
                @foreach([10, 20, 50] as $size)
                    <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} / page</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Search</button>
            <a class="btn secondary" href="{{ route('client.job-requests.create') }}">Reset</a>
        </form>

        <form class="form" method="post" action="{{ route('client.job-requests.store') }}" id="jobRequestForm" enctype="multipart/form-data">
            @csrf
            <div class="jobs-list" id="jobsList">
                @forelse($jobs as $job)
                    <div class="job-row" data-job-id="{{ $job->id }}">
                        <label class="job-check">
                            <input type="checkbox" name="jobs[{{ $job->id }}][selected]" value="1" class="job-toggle" data-job-id="{{ $job->id }}" data-fees="{{ $job->fees }}">
                            <input type="hidden" name="jobs[{{ $job->id }}][job_id]" value="{{ $job->id }}">
                            <span class="job-name">{{ $job->type_of_service }}</span>
                        </label>
                        <span class="job-desc muted">{{ \Illuminate\Support\Str::limit($job->description, 60) }}</span>
                        @if($job->category)
                            <span class="badge in_progress" style="font-size:11px">{{ $job->category }}</span>
                        @endif
                        <span class="job-price">AUD {{ number_format($job->fees, 2) }}</span>
                        <div class="job-qty">
                            <label>Qty</label>
                            <input class="input" type="number" name="jobs[{{ $job->id }}][qty]" min="1" value="1" style="width:70px">
                        </div>
                    </div>
                @empty
                    <p class="muted" style="padding:20px 0;text-align:center">No jobs found matching your search.</p>
                @endforelse
            </div>
            <div class="pagination-wrap" style="margin-top:16px">{{ $jobs->links() }}</div>
        </form>
    </section>

    <aside class="jr-create-summary">
        <div class="card" id="selectedSummary">
            <h2>Selected Jobs <span class="muted" id="selectedCount" style="font-size:13px;font-weight:400"></span></h2>
            <div id="summaryEmpty" class="muted" style="padding:20px 0;text-align:center">No jobs selected yet</div>
            <table class="table" id="summaryTable" hidden>
                <thead><tr><th>Job</th><th>QTY</th><th>Total</th><th></th></tr></thead>
                <tbody></tbody>
            </table>
            <div class="jr-summary-footer">
                <div class="jr-summary-total">
                    <span>Grand Total</span>
                    <strong id="grandTotal">AUD 0.00</strong>
                </div>
                <div class="field" style="margin-top:14px">
                    <label>Attachment <span class="muted">(optional — max 10MB)</span></label>
                    <input class="input" name="attachment" type="file" form="jobRequestForm" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                </div>
                <div class="field" style="margin-top:14px">
                    <label>Remarks <span class="muted">(optional)</span></label>
                    <textarea class="textarea" name="remarks" form="jobRequestForm" placeholder="Any additional notes...">{{ old('remarks') }}</textarea>
                </div>
                <button class="btn" type="submit" form="jobRequestForm" id="submitBtn" style="width:100%;margin-top:12px" disabled>Submit Job Request</button>
            </div>
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var STORAGE_KEY = 'jr_selected_jobs';
    var summary = document.getElementById('selectedSummary');
    var tbody = summary.querySelector('tbody');
    var summaryTable = document.getElementById('summaryTable');
    var summaryEmpty = document.getElementById('summaryEmpty');
    var grandTotalEl = document.getElementById('grandTotal');
    var selectedCountEl = document.getElementById('selectedCount');
    var submitBtn = document.getElementById('submitBtn');

    function getStored() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; } catch(e) { return {}; }
    }

    function setStored(data) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function bindJobRowEvents() {
        document.querySelectorAll('.job-row').forEach(function(row) {
            if (row.dataset.bound === 'true') return;
            row.dataset.bound = 'true';
            
            var cb = row.querySelector('.job-toggle');
            if (cb) {
                cb.addEventListener('change', updateSummary);
            }
            
            row.addEventListener('click', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'BUTTON') return;
                var checkbox = row.querySelector('.job-toggle');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
            row.style.cursor = 'pointer';
        });

        document.querySelectorAll('input[name$="[qty]"]').forEach(function(input) {
            if (input.dataset.bound === 'true') return;
            input.dataset.bound = 'true';
            input.addEventListener('input', updateSummary);
        });
    }

    function restoreSelections() {
        var stored = getStored();
        document.querySelectorAll('.job-row').forEach(function(row) {
            var cb = row.querySelector('.job-toggle');
            var qtyInput = row.querySelector('input[name$="[qty]"]');
            var jobId = cb.dataset.jobId;
            if (stored[jobId]) {
                cb.checked = true;
                qtyInput.value = stored[jobId].qty || 1;
            }
        });
    }

    function saveSelections() {
        var stored = getStored();
        document.querySelectorAll('.job-row').forEach(function(row) {
            var cb = row.querySelector('.job-toggle');
            var qtyInput = row.querySelector('input[name$="[qty]"]');
            var jobId = cb.dataset.jobId;
            if (cb.checked) {
                stored[jobId] = {
                    name: row.querySelector('.job-name').textContent,
                    fees: parseFloat(cb.dataset.fees),
                    qty: parseInt(qtyInput.value) || 1
                };
            } else {
                delete stored[jobId];
            }
        });
        setStored(stored);
    }

    function updateSummary() {
        saveSelections();
        var stored = getStored();
        tbody.innerHTML = '';
        var grand = 0;
        var count = 0;

        document.querySelectorAll('.job-row').forEach(function(row) {
            var cb = row.querySelector('.job-toggle');
            if (cb.checked) {
                row.classList.add('job-row-selected');
            } else {
                row.classList.remove('job-row-selected');
            }
        });

        Object.keys(stored).forEach(function(jobId) {
            var item = stored[jobId];
            count++;
            var total = item.fees * item.qty;
            grand += total;

            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + item.name + '</td>'
                + '<td>' + item.qty + '</td>'
                + '<td>AUD ' + total.toFixed(2) + '</td>'
                + '<td><button type="button" class="jr-remove-btn" data-remove="' + jobId + '" title="Remove">&times;</button></td>';
            tbody.appendChild(tr);
        });

        grandTotalEl.textContent = 'AUD ' + grand.toFixed(2);
        summaryTable.hidden = count === 0;
        summaryEmpty.hidden = count > 0;
        selectedCountEl.textContent = count > 0 ? '(' + count + ')' : '';
        submitBtn.disabled = count === 0;
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('jr-remove-btn')) {
            var jobId = e.target.dataset.remove;
            var stored = getStored();
            delete stored[jobId];
            setStored(stored);
            var cb = document.querySelector('.job-toggle[data-job-id="' + jobId + '"]');
            if (cb) { cb.checked = false; }
            updateSummary();
        }
    });

    window.bindJobRowEvents = bindJobRowEvents;
    
    restoreSelections();
    bindJobRowEvents();

    document.getElementById('jobRequestForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });

    updateSummary();
});
</script>
@endsection
