@extends('layouts.theme')

@section('content-header')

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Client List</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#crm-clients" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Clients : {{ count($clients) }}</a>
                        </li>
                        @if (! request()->has('user_id'))
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#archived-clients" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Archived Clients : {{ count($archivedClients) }}</a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content" id="custom-content-below-tabContent">
                        <div class="tab-pane fade show active" role="tabpanel" id="crm-clients">
                            <table id = "clients-table" class="table table-bordered table-striped">
                                <thead>
                                    <h6>Total Balance : ${{ prettyFloat($totalBalance) }}</h6>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Balance</th>
                                        <th>Assigned Websites</th>
                                        <th>Client Lead</th>
                                        <th>Project Manager</th>
                                        <th>Sync</th>
                                        <th>
                                            <button id = "sync-all-clent-info" class="btn btn-info">Sync</button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clients as $client)
                                    <tr data-client-id="{{ $client->id }}">
                                            <td>
                                                <a href = "{{ url('/client-history?clientId=' . $client->id) }}">
                                                    {{ $client->name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if( !empty($client->api_id) && isset($apiClients[$client->api_id]) )
                                                    <span data-value="{{ $apiClients[$client->api_id]['balance'] }}">${{ prettyFloat($apiClients[$client->api_id]['balance']) }}</span>
                                                @else
                                                    <span data-value="-1">$0</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $client->websites()->where('archived', 0)->count() }}
                                            </td>
                                            <td>
                                                {{ $client->clientLead ? $client->clientLead->name : '' }}
                                            </td>
                                            <td>
                                                {{ $client->projectManager ? $client->projectManager->name : '' }}
                                            </td>
                                            <td>
                                                {{ \App\Client::invoiceSyncTypes()[$client->invoice_sync_type] ?? '' }}
                                            </td>
                                            <td>
                                                <button class="btn btn-primary sync-single-client-info">Sync</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if (! request()->has('user_id'))
                            <div class="tab-pane fade show" role="tabpanel" id="archived-clients">
                                <table id = "archived-clients-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Client Name</th>
                                            <th>Assigned Websites</th>
                                            <th>Archived At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($archivedClients as $client)
                                            <tr data-client-id="{{ $client->id }}">
                                                <td>
                                                    <a href = "{{ url('/client-history?clientId=' . $client->id) }}">
                                                        {{ $client->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {{ count($client->websites()->get()) }}
                                                </td>
                                                <th>
                                                    {{ (new \Carbon\Carbon($client->archived_at))->format('m/d/Y') }}
                                                </th>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <!-- DataTables -->
    <link href="{{ mix("css/datatable.css") }}" rel="stylesheet">
@endsection
@section('javascript')
    <!-- DataTables -->
    <script src="{{ mix('js/datatable.js') }}"></script>
    <script src="{{ asset('assets/js/client/client-list.js?v=7') }}"></script>
@endsection
