@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('AI Detector'))
@section('titlebar_subtitle', __('Analyze text, comparing it against a vast database to detect AI writing'))

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
                        onclick="return sendScanRequest(event);"
                    >
                        {{ __('Scan for AI Content') }}
                    </x-button>
                </form>
            </div>

            <div class="w-full lg:w-[48%] lg:border-s lg:ps-10">
                <div class="flex flex-col items-center">
                    <h3 class="mb-7 text-center">{{ __('AI Content Report') }}</h3>

                    <div class="relative mb-11">
                        <p class="total_percent absolute left-1/2 top-[calc(50%-5px)] m-0 -translate-x-1/2 text-center text-heading-foreground">
                            <span id="ai_score" class="text-[23px] font-bold">0</span>%
                            <br>{{ __('Overall AI Detection Score') }}
                        </p>
                        <div class="relative" id="chart-credit"></div>
                    </div>

                    <div class="scan_results flex w-full flex-col items-start px-3">
                        <p class="my-2">{{ __('Highlighted sentences have the highest likelihood of being AI-generated') }}</p>
                        <div class="my-1 flex items-center gap-2">
                            
                            <span class="size-4 rounded-xl bg-[#D4534A]"></span>
                            
                            <p class="ai_likely m-0">@lang('AI Likely') - <span id="ai_likely_percent">0</span>%</p>
                            
                            <p class="m-0">{{ __('Total Characters') }}: <span id="total_length">0</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="result_template">
        <div class="lqd-plagiarism-result-item flex rounded-2xl px-4 shadow-lg shadow-black/5 dark:shadow-white/[2%]">
            <div class="flex w-full items-center justify-start gap-2 py-4">
                <p class="result_index size-6 m-0 inline-flex shrink-0 items-center justify-center rounded-full bg-heading-foreground/10 text-xs font-medium text-heading-foreground">1</p>
                <p class="text-gray-700">AI Likelihood Score: <span class="result_percent m-0 text-xs font-bold text-red-500">52%</span></p>
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
        
        
        
function highlightSentences(sentences) {
    return sentences
        .map((sentence) => {
            let color = "#D4534A80"; // Default: High AI Likelihood (Red)

            if (sentence.score >= 20) {
                color = "#1CA68580"; // Highly Human-Likely (Green)
            } else if (sentence.score >= 10) {
                color = "#E0BE5480"; // Medium Likelihood (Yellow)
            }

            return `<span class="sentence hover:opacity-80 cursor-pointer" style="background-color: ${color}; padding: 3px;">${sentence.text}</span>`;
        })
        .join(" "); // Combine into a single HTML string
}

let chart; // Declare a global chart instance    

function renderChart(score) {
    const options = {
        series: [100 - score, score], // Inverted: Human-Likely first
        labels: ["Human-Likely", "AI-Likely"],
        colors: ["#1CA685", "#D4534A"], // Green for human-likely, red for AI-likely
        chart: { type: "donut", height: 205 },
        legend: { position: "bottom" },
        plotOptions: {
            pie: { startAngle: -90, endAngle: 90, donut: { size: "75%" } },
        },
        grid: { padding: { bottom: -130 } },
        stroke: { width: 5 },
    };

    // Destroy the chart if it already exists
    if (chart) {
        chart.destroy();
    }

    // Create a new chart instance
    chart = new ApexCharts(document.getElementById("chart-credit"), options);
    chart.render();
}

function calculateAIScore(sentences) {
    if (!sentences || sentences.length === 0) {
        return 0; // Return 0 if no sentences exist
    }

    // Use the single sentence's score directly if it's the only one
    if (sentences.length === 1) {
        return sentences[0].score;
    }

    // Calculate the total human-likelihood score
    const totalScore = sentences.reduce((sum, sentence) => sum + (sentence.score || 0), 0);

    // Average the scores
    const averageScore = totalScore / sentences.length;

    // Ensure the score is between 0 and 100
    return Math.min(Math.max(averageScore, 0), 100);
}

function updateReport(data) {
    console.info("API Response:", data);

    // Handle cases where there are no sentences
    const sentences = data.result.sentences || [];
    const aiScore = calculateAIScore(sentences);

    // Update the textual fields in the report
    document.getElementById("ai_score").innerText = (100 - aiScore).toFixed(2);
    document.getElementById("ai_likely_percent").innerText = (100 - aiScore).toFixed(2); // AI Likely
    document.getElementById("total_length").innerText = data.result.length || 0;

    // Render the updated chart
    renderChart(parseFloat(100 - aiScore)); // Ensure the AI score is passed as a number

    // Generate highlighted content and update the DOM
    const highlightedContent = highlightSentences(sentences);
    $("#content_result").removeClass("hidden").html(highlightedContent);
    $("#content_scan").hide();
}

function sendScanRequest(ev) {
    ev.preventDefault();

    if ($("#content_scan").val().length < 80) {
        toastr.warning("The content length should be at least 80 characters.");
        return false;
    }

    const formData = new FormData();
    formData.append("text", $("#content_scan").val());

    Alpine.store("appLoadingIndicator").show();
    $("#scan_btn").prop("disabled", true);

    $.ajax({
        type: "post",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        url: "/dashboard/user/openai/aicontentcheck",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            $("#scan_btn").prop("disabled", false);
            Alpine.store("appLoadingIndicator").hide();

            // Update the report display
            updateReport(data);
        },
        error: function (data) {
            toastr.warning(data.responseJSON.message);
            console.log(data);
            Alpine.store("appLoadingIndicator").hide();
            $("#scan_btn").prop("disabled", false);
        },
    });
    return false;
}

document.addEventListener("DOMContentLoaded", function () {
    renderChart(0); // Initialize with 0% AI score
});

    </script>
@endpush
