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
                <p>This section shows your traffic and audience metrics for the current reporting period compared to the previous period.</p>
            </div>

            <div class="col-12 rt-boxes">

                <div class="row g-3">

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Sessions"
                            :content="number_format($report->sessions)"
                            tooltip="The total number of sessions/visits on your website."  
                            :footer="$report->getComparisonString('sessions')" 
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Pageviews"
                            :content="number_format($report->page_views)"
                            tooltip="A view of a page on your website that is tracked by tracking code. Reloads of the same page and loads initiated by using the browser back/forward buttons are included."
                            :footer="$report->getComparisonString('page_views')"    
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Total Users"
                            :content="number_format($report->total_users)"
                            tooltip="The total number of people who visited your website."    
                            :footer="$report->getComparisonString('total_users')"
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="New Users"
                            :content="number_format($report->new_users)"
                            tooltip="The number of people who visited your website for the first time."
                            :footer="$report->getComparisonString('new_users')"    
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Engagement Rate"
                            content="{{ $report->engagement_rate * 100 }}%"
                            tooltip="The percentage of engaged sessions on your website. A session is considered engaged if it lasts longer than 10 seconds, has a conversion event, or has at least two pageviews or screen views."  
                            :footer="$report->getComparisonString('engagement_rate')"  
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Events Per Session"
                            :content="$report->events_per_session"
                            tooltip="The average number of events triggered per user session on your website. An event is triggered by clicking a site link, submitting a form, playing a video, etc."
                            :footer="$report->getComparisonString('events_per_session')"    
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Avg Session Duration"
                            content="{{ $report->average_session_duration }}s"
                            tooltip="The average length of engaged sessions on your website."
                            :footer="$report->getComparisonString('average_session_duration')"    
                        >
                        </x-report.info-box>

                    </div>

                    <div class="col-6 col-lg-3">

                        <x-report.info-box
                            title="Sessions Per User"
                            :content="$report->sessions_per_user"
                            tooltip="The average number of sessions a user initiates on your website."
                            :footer="$report->getComparisonString('sessions_per_user')"    
                        >
                        </x-report.info-box>

                    </div>

                </div>

            </div>

        </div>

        <div class="row report-devicesAndBrowsers pt-4 g-3">

            <div class="col-12 col-lg-7 rt-sessions">
                <h4>Total Sessions</h4>
                <div class="rt-session-wrapper bg-white p-3">
                    <canvas id="sessionsGraph" width="100" height="60"></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-5 rd-browser">
                <h4>Browsers</h4>
                <div class="rd-browser-wrapper bg-white p-3">
                    <canvas id="browserGraph" width="100" height="87"></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-5 col-xl-4 rd-devices">
                <h4>Devices</h4>
                <div class="rd-device-wrapper bg-white p-3">
                    <canvas id="deviceGraph" width="100" height="64"></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-7 col-xl-8">
                <h4>Locations</h4>
                <div class="bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">City</th>
                                <th scope="col" class="no-whitespace">Impressions <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="The amount of times your website showed up in search results for a query"></i></th>
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
                            if ($loop->iteration == 7) {
                                break;
                            }
                            @endphp
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="row report-visitors">

            <div class="col-12 report-info-section mb-4">
                <h3>Visitor Acquisition</h3>
                <p>This section shows where your online traffic came from and how users arrived at your website for the current reporting period compared to the previous period.</p>
            </div>

            <div class="col-12 col-xl-7 rd-query">
                <h4>Top 10 Search Queries Driving Traffic to Your Website</h4>
                <div class="rd-query-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Query <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="The word or phrase a user types into a search engine"></i></th>
                                <th scope="col" class="no-whitespace">Clicks <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="How often a user clicked a search result to your website"></i></th>
                                <th scope="col" class="no-whitespace">Impressions <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="The amount of times your website showed up in search results for a query"></i></th>
                                <th scope="col" class="no-whitespace">Avg. Position <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="The average ranking of your website in search results for a given query"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $queries = unserialize($report->queries);

                                // This section has two separate blocks. The else portion is 
                                // for backwards-compatibility for the previous data structure
                                // of GSC return data.
                            @endphp

                            @if (empty($queries))
                                <tr>
                                    <td>No query data this month.<br/>Check back next month!</td>
                                </tr>
                            @else
                                @if (isset($queries[0]))
                                    @foreach ($queries as $query) 
                                    <tr {{ $query['position'] <= 10 ? "class=table-active" : "" }}>
                                        <td class="w-100">{{ $query['query'] }}</td>
                                        <td>{{ $query['clicks'] }}</td>
                                        <td>{{ $query['impressions'] }}</td>
                                        <td>{{ round($query['position'], 2) }}</td>
                                    </tr>

                                    @php 
                                    if ($loop->iteration == 10) {
                                        break;
                                    }
                                    @endphp
                                    @endforeach
                                @else
                                    @foreach ($queries as $query => $impressions) 
                                    <tr>
                                        <td class="w-100">{{ $query }}</td>
                                        <td>-</td>
                                        <td>{{ $impressions }}</td>
                                        <td>-</td>
                                    </tr>

                                    @php 
                                    if ($loop->iteration == 10) {
                                        break;
                                    }
                                    @endphp
                                    @endforeach
                                @endif
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-xl-5 rd-channel">
                <div class="row g-3">
                    
                    <div class="col-12">
                        <h4>Website Traffic Channels</h4>
                        <div class="rd-channel-wrapper bg-white p-3">
                            <canvas id="channelGraph" width="100" height="80"></canvas>
                        </div>
                    </div>

                    <div class="col-6">

                        <x-report.info-box
                            title="Clicks*"
                            :content="number_format($report->getTotalClicks())"
                            tooltip="*Data is pulled from the top 100 queries driving traffic to your website."    
                            :footer="$report->getClicksComparisonString()"
                        >
                        </x-report.info-box>
                        
                    </div>
                    <div class="col-6">

                        <x-report.info-box
                            title="Impressions*"
                            :content="number_format($report->getTotalImpressions())"
                            tooltip="*Data is pulled from the top 100 queries driving traffic to your website."    
                            :footer="$report->getImpressionsComparisonString()"
                        >
                        </x-report.info-box>

                    </div>

                </div>
            </div>

        </div>

        <div class="row report-pagescities">

            <div class="report-info-section">
                <h3>Site Behavior</h3>
                <p>This section shows the metrics of your website content<br/>during the current reporting period compared to the previous period.</p>
            </div>

            <div class="col-12 col-lg rd-channel">
                <div class="report-info-section">
                    <h3>Most Visited Pages</h3>
                    <p>These are the most visited<br/>pages on your website.</p>
                </div>

                <div class="rd-pages-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Page Title</th>
                                <th scope="col">Sessions</th>
                                <th scope="col" class="no-whitespace">% Change</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach (unserialize($report->pages) as $query => $sessions) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="w-100">{{ $query }}</td>
                                <td class="text-end">{{ $sessions }}</td>
                                <td class="text-end">{!! $report->getPageComparisonString($query) !!}</td>
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

            @if ($report->post_data != NULL)
            <div class="col-12 col-lg-6 rd-channel">
                <div class="report-info-section">
                    <h3>New Content Added</h3>
                    <p>These are the new blog posts and pages that were added to your website during this reporting period.</p>
                </div>

                <div class="rd-pages-wrapper bg-white">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Post Title</th>
                                <th scope="col">Sessions</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach (unserialize($report->post_data) as $post) 
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="w-100">{{ $post['post_title'] }}</td>
                                <td class="text-end">{{ $post['sessions'] }}</td>
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
            @endif

            @if ($report->reviews != NULL)
            <div class="col-12 report-reviews">

                <div class="report-info-section">
                    <h3>Recent Reviews</h3>
                    <p>This section shows your most recent Google reviews. We recommend garnering at least one new Google review each week to show users and Google that you are active online and take your reputation seriously.</p>
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

        <div class="report-footer-logo text-center">
            <a href="https://mdpmmarketing.com/" target="_blank"><img src="{{ Vite::asset('resources/images/mdpm-logo-2024.png') }}" alt="MDPM Marketing" /></a>
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
            $dateSessionData = $report->getFormattedSessionData(); 
            
            if ($report->hasValidComparisonReport()) {
                $comparisonDateSessionData = $report->comparisonReport->getFormattedSessionData();
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