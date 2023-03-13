@extends('layouts.app')

@section('content')

@if ($report->exp_date !== null && date('Y-m-d') > date('Y-m-d', strtotime($report->exp_date)) && !Auth::check())

    <div class="reportExpiration text-center">
        <h1>This Report Has Expired!</h1>
        <p>Please reach out to MDPM Consulting for your new report.</p>
    </div>

@else

@if ($report->exp_date !== null && date('Y-m-d') > date('Y-m-d', strtotime($report->exp_date)) && Auth::check())
    <h2 class="bg-danger text-center p-4 text-white mb-4">You are viewing an expired report</h2>
@endif

<div id="reportBody">
    <div class="container-fluid">

        @php
            $startDate = strtotime($report->start_date);
            $endDate = strtotime($report->end_date);
        @endphp

        <div class="row report-header">
            
            <div class="col-12 col-lg-6 report-logo @if ($property->logo_dark_background) bg-reportdark @endif">
                <x-property.logo :property="$property" />
            </div>

            <div class="col-12 col-lg-6 report-info">
                <div class="report-info-inner">
                    <h1>{{ date('F', $startDate) }} SEO Report</h1>
                    <h4>{{ $property->url }}</h4>
                    <p>{{ date('F d, Y', $startDate) }} - {{ date('F d, Y', $endDate) }}</p>
                </div>
            </div>

        </div>

        <div class="row report-trafficAndAudience">
            
            <div class="col-12 rt-info report-info-section">
                <h3>Traffic and Audience Overview</h3>
                <p>This section shows your traffic and audience metrics for the current report period compared to the previous period.</p>
            </div>

            <div class="col-12 col-lg-5 rt-boxes">

                <div class="row g-3">

                    <div class="col-5">

                        <x-report.info-box
                            title="Total Users"
                            :content="number_format($report->total_users)"
                            tooltip="The total number of active users."    
                            :footer="$report->getUsersComparisonString()"
                        >
                        </x-report.info-box>

                    </div>
                    <div class="col-7">

                        <x-report.info-box
                            title="Engagement Rate"
                            content="{{ $report->engagement_rate * 100 }}%"
                            tooltip="The percentage of engaged sessions (Engaged sessions divided by Sessions)."  
                            :footer="$report->getEngagementRateComparisonString()"  
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-5">

                        <x-report.info-box
                            title="Sessions"
                            :content="number_format($report->sessions)"
                            tooltip="The number of sessions that began on your site."  
                            :footer="$report->getSessionsComparisonString()" 
                        >
                        </x-report.info-box>

                    </div>
                    <div class="col-7">

                        <x-report.info-box
                            title="Events per session"
                            :content="$report->events_per_session"
                            tooltip="The average number of times your users triggered an event during their sessions."
                            :footer="$report->getEventsPerSessionComparisonString()"    
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-5">

                        <x-report.info-box
                            title="Views"
                            :content="number_format($report->page_views)"
                            tooltip="The number of web pages your users saw. Repeated views of a single screen or page are counted."
                            :footer="$report->getViewsComparisonString()"    
                        >
                        </x-report.info-box>

                    </div>
                    <div class="col-7">

                        <x-report.info-box
                            title="Sessions per user"
                            :content="$report->sessions_per_user"
                            tooltip="The average number of sessions per user."
                            :footer="$report->getSessionsPerUserComparisonString()"    
                        >
                        </x-report.info-box>

                    </div>

                </div>

            </div>

            <div class="col-12 col-lg-7 rt-sessions">

                <div class="rt-session-wrapper bg-white p-3">
                    <canvas id="sessionsGraph" width="100" height="55"></canvas>
                </div>

            </div>

        </div>

        <div class="row report-devicesAndBrowsers">

            <div class="col-12 report-info-section">
                <h3>Visitor Devices</h3>
                <p>This section shows how your website visitors access and view your website.</p>
            </div>

            <div class="col-12 col-lg-7 rd-browser">
                <div class="rd-browser-wrapper bg-white p-3">
                    <canvas id="browserGraph" width="100" height="69"></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-5 rd-devices">
                <div class="rd-device-wrapper bg-white p-3">
                    <canvas id="deviceGraph" width="100" height="64"></canvas>
                </div>
            </div>

        </div>

        <div class="row report-visitors">

            <div class="col-12 report-info-section">
                <h3>Visitor Acquisition</h3>
                <p>This section shows where your online traffic comes from and what search queries users type into the search engine to arrive at your website.</p>
            </div>

            <div class="col-12 col-lg-6 rd-query">
                <div class="rd-query-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Query</th>
                                <th scope="col">Impressions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty(unserialize($report->queries)))
                                <tr>
                                    <td>No query data this month.<br/>Check back next month!</td>
                                </tr>
                            @else
                                @foreach (unserialize($report->queries) as $query => $impressions) 
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="w-100">{{ $query }}</td>
                                    <td>{{ $impressions }}</td>
                                </tr>

                                @php 
                                if ($loop->iteration == 10) {
                                    break;
                                }
                                @endphp
                                @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-lg-6 rd-channel">
                <div class="rd-channel-wrapper bg-white p-3">
                    <canvas id="channelGraph" width="100" height="91"></canvas>
                </div>
            </div>

        </div>

        <div class="row report-pagescities">

            <div class="col-12 col-lg-6 rd-channel">
                <div class="report-info-section">
                    <h3>Most Visited Pages</h3>
                    <p>These are the top visited pages<br/>on your website.</p>
                </div>

                <div class="rd-pages-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Page Title</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($report->getDatabaseArrayKeys('pages') as $page) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="w-100">{{ $page }}</td>
                            </tr>

                            @php 
                            if ($loop->iteration == 10) {
                                break;
                            }
                            @endphp
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-lg-6 rd-query">
                <div class="report-info-section">
                    <h3>Visitors by City</h3>
                    <p>These are the top cities that the majority of your website traffic comes from.</p>
                </div>

                <div class="rd-device-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">City</th>
                                <th scope="col">Impressions</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach (unserialize($report->cities) as $city => $impressions) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="w-100">{{ $city }}</td>
                                <td>{{ $impressions }}</td>
                            </tr>

                            @php 
                            if ($loop->iteration == 10) {
                                break;
                            }
                            @endphp
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @if ($report->reviews != NULL)
            <div class="col-12 report-reviews">

                <div class="report-info-section">
                    <h3>Recent Reviews</h3>
                    <p>These are your most recent Google reviews. We recommend garnering at least 1 Google review each week to show potential patients that you are active online.</p>
                </div>

                <div class="row g-4">
                    @foreach (unserialize($report->reviews) as $review) 
                    <div class="col-12 col-md-6 d-flex">
                        <div class="review-card card">
                            <div class="card-body">
                                
                                <div class="review-content">
                                    @if ($review->text != '')
                                    {{ $review->text }}
                                    @else
                                    <span class="text-muted">No review content provided</span>
                                    @endif
                                </div>

                                <div class="review-footer align-items-center justify-content-end row text-end gy-2">
                                    <div class="rf-time col-auto">
                                        {{ date('F j, o', $review->time) }}<br/>
                                        <span class="text-muted">{{ $review->relative_time_description }}</span>
                                    </div>
                                    <div class="rd-rating col-auto">
                                        @for ($i = 0; $i < 5; $i++)
                                            @if ($i < $review->rating)
                                            <i class="bi bi-star-fill"></i>
                                            @else
                                            <i class="bi bi-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @if ($loop->iteration == 2)
                        @php break; @endphp
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>

    </div>

    <div id="reportFooter">
        
        <h2>We Are Here To Help You Reach Your Goals</h2>

        <p>We are here to help you grow your online footprint and - as a result - get new patients in your chair and keep your current patients coming back. SEO metrics and numbers are great, but they don't tell the whole story.</p>
        <p>We need to know what is going on in your office so that we can adjust your strategy to ensure we're attracting the patients you want.</p>
        <p>Have questions? Need help interpreting this report? Let us know!</p>

        <div class="report-footer-logo text-center mt-5">
            <img src="{{ Vite::asset('resources/images/mdpm-logo.png') }}" alt="MDPM Consulting" />
        </div>

    </div>

</div>

@endif

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>

    generateSessionsGraph();
    generateBrowsersGraph();
    generateDevicesGraph();
    generateChannelsGraph();

    function generateSessionsGraph() 
    {
        @php 
            $dateSessionData = $report->getFormattedArray('date_session', true); 
            
            if ($report->hasValidComparisonReport()) {
                $comparisonDateSessionData = $report->comparisonReport->getFormattedArray('date_session', true);
            }
        @endphp
        const sessionsGraph = new Chart($('#sessionsGraph'), {
            type: 'line',
            data: {
                labels: [ {!! $dateSessionData[0] !!} ],
                datasets: [
                    {
                        label: 'Current Period',
                        data: [ {!! $dateSessionData[1] !!} ],
                        backgroundColor: [
                            '#0d6efd'
                        ],
                        borderColor: [
                            '#0d6efd'
                        ],
                        borderWidth: 3
                    },
                    @if ($report->hasValidComparisonReport())
                    {
                        label: 'Previous Period',
                        data: [ {!! $comparisonDateSessionData[1] !!} ],
                        backgroundColor: [
                            'orange'
                        ],
                        borderColor: [
                            'orange'
                        ],
                        borderWidth: 1
                    }
                    @endif
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function generateBrowsersGraph() 
    {
        @php $browserData = $report->getFormattedArray('browsers'); @endphp
        const browserGraph = new Chart($('#browserGraph'), {
            type: 'bar',
            data: {
                labels: [ {!! $browserData[0] !!} ],
                datasets: [{
                    label: 'Sessions',
                    data: [ {!! $browserData[1] !!} ],
                    backgroundColor: [
                        '#0d6efd'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function generateDevicesGraph() 
    {
        @php $deviceData = $report->getFormattedArray('devices'); @endphp
        const deviceGraph = new Chart($('#deviceGraph'), {
            type: 'pie',
            data: {
                labels: [ {!! $deviceData[0] !!} ],
                datasets: [{
                    data: [ {!! $deviceData[1] !!} ],
                    backgroundColor: [
                        '#0d6efd',
                        '#00b6cb',
                        '#f10096'
                    ],
                    borderWidth: 2
                }]
            },
        });
    }

    function generateChannelsGraph() 
    {
        @php $channelData = $report->getFormattedArray('channels'); @endphp
        const channelGraph = new Chart($('#channelGraph'), {
            type: 'bar',
            data: {
                labels: [ {!! $channelData[0] !!} ],
                datasets: [{
                    label: 'Sessions',
                    data: [ {!! $channelData[1] !!} ],
                    backgroundColor: [
                        '#0d6efd'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

</script>
@endsection