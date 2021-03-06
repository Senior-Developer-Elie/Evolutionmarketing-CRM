@extends('layouts.theme')

@section('content-header')

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <i class="fa fa-calendar-times-o"></i>

                    <h3 class="card-title">Website List</h3>

                    <div class="card-tools pull-right">
                        <a href="{{ route('websites.create') }}">
                            <button type="button" class="btn btn-primary pull-right">
                                <i class="fa fa-plus"></i> Add Website
                            </button>
                        </a>
                        <button id="export-websites-budget-button" type="button" class="btn btn-info pull-right mr-3">
                            <i class="fas fa-download"></i> Export Websites Budget
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#websites-wrapper" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Websites : {{ count($websites) }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#archived-websites-wrapper" role="tab" aria-controls="custom-content-below-home" aria-selected="true">Archived Websites : {{ count($archivedWebsites) }}</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="custom-content-below-tabContent">
                        <div class="tab-pane fade show active" role="tabpanel" id="websites-wrapper">
                            
                            <select class="form-control" id="blog-industry-filter">
                                <option value="">All Industries</option>
                                @foreach ($blogIndustries as $blogIndustry)
                                    <option value="{{ $blogIndustry->id }}" {{ Request::input('blog_industry_id') == $blogIndustry->id ? 'selected' : '' }}>
                                        {{ $blogIndustry->name }} ({{ $blogIndustry->active_websites_count }})
                                    </option>
                                @endforeach
                            </select>

                            <select class="form-control" id="affilliate-filter">
                                <option value="">All Affilates</option>
                                @foreach ($affiliateTypes as $key => $value)
                                    <option value="{{ $key }}" {{ Request::input('affilliate_id') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>

                            <select class="form-control" id="website-type-filter">
                                <option value="">All Types</option>
                                @foreach (\App\Http\Helpers\WebsiteHelper::getAllWebsiteTypes() as $key => $value)
                                    <option value="{{ $key }}" {{ Request::input('website_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>

                            <select class="form-control" id="sync-status-filter">
                                <option value="">All Websites</option>
                                <option value="synced" {{ Request::input('sync_status') == 'synced' ? 'selected' : '' }}>
                                    Synced
                                </option>
                                <option value="not-synced" {{ Request::input('sync_status') == 'not-synced' ? 'selected' : '' }}>
                                    Not Synced
                                </option>
                            </select>

                            @include('manage-website.sections.website-table', [ 'websites' => $websites, 'archived' => false ])
                        </div>
                        <div class="tab-pane fade show" role="tabpanel" id="archived-websites-wrapper">
                            @include('manage-website.sections.website-table', [ 'websites' => $archivedWebsites, 'archived' => true ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="websites-budget-export-form" role="form" action="{{ route("websites.export-budget") }}" target="_blank" method="POST" style="display:none;">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>
@endsection

@section('css')
    <!-- DataTables -->
    <link href="{{ mix("css/datatable.css") }}" rel="stylesheet">

    <!-- Jquery Editable -->
    <link rel="stylesheet" href="{{ asset('assets/lib/jquery-editable/css/tip-yellowsimple.css?v=2') }}">
    <link rel="stylesheet" href="{{ asset('assets/lib/jquery-editable/css/jquery-editable.css') }}">


    <link rel="stylesheet" href="{{ asset('assets/css/website-list.css?v=10') }}">
@endsection

@section('javascript')
    <script>
        var allWebsiteTypes = {!! json_encode($websiteTypes) !!};
        var allAffiliateTypes = {!! json_encode($affiliateTypes) !!};
        var allDNSTypes = {!! json_encode($dnsTypes) !!};
        var allPaymentGateways = {!! json_encode($paymentGateways) !!};
        var allEmailTypes = {!! json_encode($emailTypes) !!};
        var allSitemapTypes = {!! json_encode($sitemapTypes) !!};
        var allLeftReviewTypes = {!! json_encode($leftReviewTypes) !!};
        var allPortfolioTypes = {!! json_encode($portfolioTypes) !!};
        var allYextTypes = {!! json_encode($yextTypes) !!};
        var allIndustries = {!! json_encode($blogIndustriesForInline) !!};
    </script>

    <script src="{{ mix('js/datatable.js') }}"></script>

    <script src="{{ asset('assets/lib/jquery-editable/js/jquery.poshytip.js') }}"></script>
    <script src="{{ asset('assets/lib/jquery-editable/js/jquery-editable-poshytip.js') }}"></script>

    <script src="{{ asset('assets/js/website/website-list.js?v=51') }}"></script>
@endsection
