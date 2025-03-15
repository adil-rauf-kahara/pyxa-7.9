@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('AI Plagiarism'))
@section('titlebar_subtitle', __('Analyze text, comparing it against a vast database of online content to identify potential plagiarism'))

@section('content')
    <div class="py-10">
        <div class="lqd-plagiarism-wrap flex flex-wrap justify-between gap-y-5">
            <div class="w-full lg:w-[48%]">
                <form
                    class="flex flex-col gap-5"
                    id="scan_content_form"
                    onsubmit="return sendScanRequest(event);"
                    enctype="multipart/form-data"
                >
                     <x-button
                        class="py-0.5"
                        id="generate_speech_button"
                        tag="button"
                        type="button"
                        size="lg"
                    >
                    {{ __('Per Generation = 10x Word Tokens') }}
                </x-button>
                    
                    <x-card size="xs">
                        <h4 class="my-0">{{ __('Add Content') }}
                            <small class="ms-3 font-normal" id="content_length">0/5000</small>
                        </h4>
                    </x-card>

                    <x-forms.input
                        class="tinymce h-[600px] border-border"
                        id="content_scan"
                        name="content_scan"
                        rows="20"
                        required
                        type="textarea"
                    />

                    <div
                        class="tinymce hidden h-[600px] overflow-y-scroll rounded-xl border"
                        id="content_result"
                        name="content_result"
                    ></div>

                    <x-button
                        id="scan_btn"
                        size="lg"
                        form="scan_content_form"
                        type="submit"
                        onclick="return sendScanRequest(event)"
                    >
                        {{ __('Scan for Plagiarism') }}
                    </x-button>
                </form>
            </div>

            <div class="w-full lg:w-[48%] lg:border-s lg:ps-10">
                <h3 class="mb-7 text-center">{{ __('Plagiarism Report') }}</h3>

                <div class="relative mb-11">
                    <p class="total_percent absolute left-1/2 top-[calc(50%-5px)] m-0 -translate-x-1/2 text-center text-heading-foreground">
                        <span id="plagiarism_score" class="text-[23px] font-bold">0</span>%
                        <br>{{ __('Match') }}
                    </p>
                    <div class="relative" id="chart-credit"></div>
                </div>

                <x-card class="mb-5" size="xs">
                    <h4 class="my-0">{{ __('Result Summary') }}</h4>
                    <p class="m-0">{{ __('Scan Time') }}: <span id="scan_time">-</span></p>
                    <p class="m-0">{{ __('Total Words') }}: <span id="word_count">-</span></p>
                    <p class="m-0">{{ __('Plagiarized Words') }}: <span id="plagiarized_words">-</span></p>
                    <p class="m-0">{{ __('Identical Words') }}: <span id="identical_words">-</span></p>
                    <p class="m-0">{{ __('Similar Words') }}: <span id="similar_words">-</span></p>
                    <p class="m-0">{{ __('Sources Found') }}: <span id="source_count">-</span></p>
                </x-card>

                <div class="lqd-plagiarism-results scan_results flex w-full flex-col gap-5"></div>
            </div>
        </div>
    </div>

    <template id="result_template">
        <div class="lqd-plagiarism-result-item flex rounded-2xl px-4 shadow-lg shadow-black/5 dark:shadow-white/[2%]">
            <div class="flex w-4/5 items-center justify-start gap-2 py-4">
                <p class="result_index size-6 m-0 inline-flex shrink-0 items-center justify-center rounded-full bg-heading-foreground/10 text-xs font-medium text-heading-foreground">1</p>
                <a class="result_url flex w-full items-center gap-2 truncate text-xs" href="#" target="_blank">
                    <x-tabler-link class="size-4" />
                    <span class="result_url_p">Source Title</span>
                </a>
            </div>
            <div class="w-1/5 border-s py-4 text-center">
                <p class="m-0 text-2xs font-medium">{{ __('Match') }}</p>
                <p class="result_percent m-0 text-xs font-bold text-red-500">52%</p>
            </div>
            <div class="flex flex-col p-4 text-xs">
                <p><strong>{{ __('Author') }}:</strong> <span class="source_author">-</span></p>
                <p><strong>{{ __('Published Date') }}:</strong> <span class="source_published_date">-</span></p>
                <p><strong>{{ __('Description') }}:</strong> <span class="source_description">-</span></p>
            </div>
        </div>
    </template>
@endsection

@push('script')
    <script src="/themes/default/assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    
    <script>
        $("#content_scan").on('input', function(e) {
            $("#content_length").text($(this).val().length + "/5000");
        });
        
        let chart; // Declare chart as a global variable

function renderChart(percent) {
    const options = {
        series: [percent, 100 - percent],
        labels: ['Plagiarized', 'Unique'],
        colors: ['#D4534A', '#1CA685'],
        chart: { type: 'donut', height: 205 },
        legend: { position: 'bottom' },
        plotOptions: {
            pie: { startAngle: -90, endAngle: 90, donut: { size: '75%' }}
        },
        grid: { padding: { bottom: -130 }},
        stroke: { width: 5 }
    };

    // Destroy the chart if it already exists
    if (chart) {
        chart.destroy();
    }

    // Create a new chart instance
    chart = new ApexCharts(document.getElementById('chart-credit'), options);
    chart.render();
}

    
        
        function updateReport(data) {
    // Update report fields with response data
    document.getElementById('plagiarism_score').innerText = data.$result.result.score ?? 0;
    document.getElementById('scan_time').innerText = new Date(data.$result.scanInformation.scanTime);
    document.getElementById('word_count').innerText = data.$result.result.textWordCounts ?? '0';
    document.getElementById('plagiarized_words').innerText = data.$result.result.totalPlagiarismWords ?? '0';
    document.getElementById('identical_words').innerText = data.$result.result.identicalWordCounts ?? '0';
    document.getElementById('similar_words').innerText = data.$result.result.similarWordCounts ?? '0';
    document.getElementById('source_count').innerText = data.$result.result.sourceCounts ?? '0';

    const sources = data.$result.sources;
    $(".scan_results").empty();
    if (sources && sources.length > 0) {
        sources.forEach((source, index) => {
            const template = document.querySelector('#result_template').content.cloneNode(true);
            $(template.querySelector('.result_index')).text(index + 1);
            $(template.querySelector('.result_url_p')).text(
                (source.title || 'Source').split(' ').slice(0, 2).join(' ')
            );
            $(template.querySelector('.result_url')).attr('href', source.url || '#');
            $(template.querySelector('.result_percent')).text(source.score + '%');
            $(template.querySelector('.source_author')).text(source.author || '-');
            $(template.querySelector('.source_published_date')).text(new Date(source.publishedDate * 1000).toLocaleDateString() || '-');
            $(template.querySelector('.source_description')).text(source.description || '-');
            $(".scan_results").append(template);
        });
    }

    // Render and update the chart
    const percent = data.$result.result.score ?? 0;
    renderChart(percent);

    // Force the chart to resize to ensure correct rendering
    setTimeout(() => chart.redraw(), 100);
}


        function sendScanRequest(ev) {
            ev.preventDefault(); // Prevent form submission and page reload

            if ($("#content_scan").val().length < 80) {
                toastr.warning('The length of content should be greater than 80 characters.');
                return;
            }

            const formData = new FormData();
            formData.append('text', $("#content_scan").val());

            // Show loading indicator and disable button
            Alpine.store('appLoadingIndicator').show();
            $('#scan_btn').prop('disabled', true);

            $.ajax({
                type: "post",
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                url: "/dashboard/user/openai/plagiarismcheck",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    // Re-enable button and hide loading indicator
                    $('#scan_btn').prop('disabled', false);
                    Alpine.store('appLoadingIndicator').hide();

                    // Call function to update report display with returned data
                    updateReport(data);
                },
                error: function(data) {
                    toastr.warning(data.responseJSON.message);
                    Alpine.store('appLoadingIndicator').hide();
                    $('#scan_btn').prop('disabled', false);
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            renderChart(0);
        });
    </script>
@endpush
