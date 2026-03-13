<x-layouts.public meta-title="Time Tracking for Freelance Developers: A Practical Guide · SoloHours">
    <section class="mx-auto max-w-5xl space-y-10">
        <div class="max-w-3xl space-y-4">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Guides</p>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">Time Tracking for Freelance Developers: A Practical Guide</h1>
            <p class="text-base text-base-content/75">
                Many freelance developers assume they know how long their work takes. In practice, most engineering
                tasks expand beyond the original estimate. Without tracking billable hours, it becomes difficult to
                invoice accurately or evaluate whether a project is actually profitable.
            </p>
            <p class="text-sm text-base-content/60">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">1. Why Developers Misjudge Time</h2>
            <p class="text-sm text-base-content/75">
                Software work rarely follows a predictable schedule. A task that looks simple can become complicated
                once edge cases appear, dependencies break, or unexpected debugging is required. Developers often
                remember only the “productive coding time” and forget the additional hours spent investigating issues.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">2. Track Work Sessions, Not Just Tasks</h2>
            <p class="text-sm text-base-content/75">
                Instead of estimating time at the end of the day, record actual work sessions while they happen.
                Short focused sessions — even 20 to 30 minutes — accumulate throughout the day. These smaller blocks
                are usually forgotten when reconstructing time later.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">3. Separate Clients and Projects</h2>
            <p class="text-sm text-base-content/75">
                Developers often work on multiple repositories, environments, or services for a single client.
                Separating time entries by project makes it easier to generate invoices and explain what work was
                performed during a billing cycle.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">4. Use Time Data to Improve Estimates</h2>
            <p class="text-sm text-base-content/75">
                Historical time entries become valuable reference points. Over time you will learn how long database
                migrations, API integrations, or UI adjustments typically take in your workflow.
            </p>
        </section>

        <section class="max-w-3xl space-y-3 pb-2 text-sm text-base-content/75">
            <h2 class="text-lg font-semibold text-base-content">5. Keep the Tool Simple</h2>
            <p>
                Time tracking only works when it adds minimal friction to your workflow. A simple timer and clear
                project structure are usually more effective than complex productivity dashboards.
            </p>
            <p>
                SoloHours was designed as a lightweight tracker for freelancers who want to record work sessions and
                export timesheets for invoicing. You can
                <a href="{{ route('signup') }}" class="text-primary hover:underline">create an account here</a>.
            </p>
        </section>
    </section>
</x-layouts.public>