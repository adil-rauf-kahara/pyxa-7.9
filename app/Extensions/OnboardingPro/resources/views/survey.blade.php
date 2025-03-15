@php
    $survey = \App\Extensions\OnboardingPro\System\Models\Survey::query()->where('status', true)->first();
@endphp

@auth()
    @if (auth()->user()->type === \App\Enums\Roles::USER && $survey)
        @php
            $display = \App\Extensions\OnboardingPro\System\Models\SurveyUser::query()
                ->where('user_id', auth()->user()->id)
                ->where('survey_id', $survey->id)
                ->first();
        @endphp

        @if (!$display)
            <div
                class="survey-container fixed bottom-12 left-1/2 z-50 w-96 -translate-x-1/2 transform rounded-lg bg-background p-5 shadow-xl"
                id="survey-extension"
                x-data="{ open: {{ session('showSurvey') ? 'true' : 'false' }} }"
                x-show="open"
                x-cloak
                style="background-color: {{ $survey->background_color }}"
            >

                <button
                    class="close-button absolute right-2 top-0 text-2xl text-white mix-blend-difference focus:outline-none"
                    @click="open = false; {{ session()->forget('showSurvey') }}"
                    aria-label="Close"
                >
                    &times;
                </button>
                <div class="survey-content">
                    <p
                        class="survey-question mb-4 text-center text-lg font-semibold"
                        style="color: {{ $survey->text_color }}"
                    >
                        {{ $survey->description }}
                    </p>
                    <div class="survey-options flex justify-center gap-4">
                        <button
                            class="emoji-option inline-grid size-8 place-content-center rounded-full border-none bg-transparent text-2xl transition-all hover:scale-110 focus:outline-none"
                            onclick="sendRequest(1, {{ $survey->id }})"
                        >😬</button>
                        <button
                            class="emoji-option inline-grid size-8 place-content-center rounded-full border-none bg-transparent text-2xl transition-all hover:scale-110 focus:outline-none"
                            onclick="sendRequest(2, {{ $survey->id }})"
                        >😟</button>
                        <button
                            class="emoji-option inline-grid size-8 place-content-center rounded-full border-none bg-transparent text-2xl transition-all hover:scale-110 focus:outline-none"
                            onclick="sendRequest(3, {{ $survey->id }})"
                        >😐</button>
                        <button
                            class="emoji-option inline-grid size-8 place-content-center rounded-full border-none bg-transparent text-2xl transition-all hover:scale-110 focus:outline-none"
                            onclick="sendRequest(4, {{ $survey->id }})"
                        >🙂</button>
                        <button
                            class="emoji-option inline-grid size-8 place-content-center rounded-full border-none bg-transparent text-2xl transition-all hover:scale-110 focus:outline-none"
                            onclick="sendRequest(5, {{ $survey->id }})"
                        >🤩</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endauth

<script>
    function sendRequest(point, surveyId) {
        const url = `{{ route('dashboard.admin.onboarding-pro.survey.display', ['point' => ':point', 'id' => ':id']) }}`.replace(':id', surveyId).replace(':point', point);

        fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
            })
            .then(data => {
                const alertElement = document.getElementById('survey-extension');
                if (alertElement) {
                    alertElement.style.display = 'none';
                }
            })
            .catch(error => {});
    }
</script>
