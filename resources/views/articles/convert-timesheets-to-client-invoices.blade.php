<x-layouts.public meta-title="How Freelance Developers Turn Timesheets into Client Invoices · Maulana Kurniawan">
    <section class="mx-auto max-w-5xl space-y-10">
        <div class="max-w-3xl space-y-4">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Guides</p>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">How Freelance Developers Turn Timesheets into Client Invoices</h1>
            <p class="text-base text-base-content/75">
                For freelance developers billing by the hour, a clean timesheet is often the foundation of a reliable
                invoice. Without detailed records, billing becomes guesswork and clients may question how time was spent.
            </p>
            <p class="text-sm text-base-content/60">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">1. Record Clear Descriptions</h2>
            <p class="text-sm text-base-content/75">
                Each time entry should include a short description of the work performed. Examples include debugging,
                implementing an API endpoint, or reviewing pull requests. These short notes make invoices easier for
                clients to understand.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">2. Group Entries by Billing Period</h2>
            <p class="text-sm text-base-content/75">
                Most freelance agreements define a weekly or monthly billing cycle. Organizing time entries within
                these periods ensures the invoice reflects exactly what work was completed during that timeframe.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">3. Calculate Billable Hours</h2>
            <p class="text-sm text-base-content/75">
                After grouping entries, calculate the total hours spent for the billing period. This number is then
                multiplied by the agreed hourly rate to determine the invoice total.
            </p>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">4. Attach the Timesheet</h2>
            <p class="text-sm text-base-content/75">
                Attaching a timesheet breakdown alongside the invoice increases transparency. Clients can review the
                exact work performed without requesting additional clarification.
            </p>
        </section>

        <section class="max-w-3xl space-y-3 pb-2 text-sm text-base-content/75">
            <h2 class="text-lg font-semibold text-base-content">5. Archive Historical Data</h2>
            <p>
                Keeping past timesheets allows freelancers to evaluate how projects evolve over time. This information
                can help improve pricing strategies and project estimation in the future.
            </p>
            <p>
                this site allows you to export clean timesheets for invoicing whenever needed. You can
                <a href="{{ route('contact.show') }}" class="text-primary hover:underline">reach out</a>
                to track your billable hours.
            </p>
        </section>
    </section>
</x-layouts.public>