<x-layouts.public meta-title="User Privacy Policy · SoloHours">
    <section class="mx-auto max-w-5xl space-y-10">
        <div class="max-w-3xl space-y-4">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Legal</p>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">Privacy Policy</h1>
            <p class="text-base text-base-content/75">
                This Privacy Policy explains what data SoloHours collects, how we use it, when it may be shared, and
                the choices you may have regarding your personal data.
            </p>
            <p class="text-sm text-base-content/60">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">1. Data We Collect</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Account data such as name, email, and authentication details.</li>
                <li>Product data such as clients, projects, time entries, and report/export settings.</li>
                <li>Billing identifiers and subscription metadata needed to manage subscriptions and transactions.</li>
                <li>Support and communication records when you contact us.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">2. How We Use Data</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Provide core product functionality and account access.</li>
                <li>Process subscriptions, billing events, and account notices.</li>
                <li>Improve reliability, quality, and security.</li>
                <li>Respond to customer support and legal obligations.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">3. Payments and Processors</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Payment transactions are handled by Paddle as Merchant of Record and processed under Paddle's own policies.</li>
                <li>SoloHours does not store full payment card details on its own servers.</li>
                <li>We use trusted processors for hosting, email delivery, anti-abuse, and billing operations.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">4. Billing and Subscription Data</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>We process subscription status, plan selection, and billing identifiers to manage plan limits and account access.</li>
                <li>We record plan and subscription changes that affect entitlements so account access and support records remain accurate.</li>
                <li>We retain billing-related records such as invoices and payment confirmations as required for legal, tax, accounting, and dispute handling purposes.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">5. Security and Anti-Abuse</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>We process sign-in, session, and verification events to help secure accounts.</li>
                <li>Public signup/contact flows may use Cloudflare Turnstile to reduce spam and abuse.</li>
                <li>Turnstile processing is subject to Cloudflare's own privacy terms.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-4">
            <h2 class="text-xl font-semibold">6. Sharing, Retention, and Transfers</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-base-content/75">
                <li>We share data only with service providers necessary to operate SoloHours, or when required by law.</li>
                <li>We retain data while accounts are active and as needed for security, legal, tax, or dispute purposes.</li>
                <li>After account deletion, product data is removed according to our deletion workflows, subject to legal retention duties.</li>
                <li>Your data may be processed in countries other than your own, depending on infrastructure and provider locations.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-4">
            <h2 class="text-xl font-semibold">7. Your Rights and Choices</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-base-content/75">
                <li>Depending on your location, you may have rights to access, correct, export, or delete personal data.</li>
                <li>You may also have rights to object to or restrict certain processing activities.</li>
                <li>SoloHours uses essential cookies/session technologies required for authentication and core functionality.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3 pb-2 text-sm text-base-content/75">
            <h2 class="text-lg font-semibold text-base-content">8. Policy Updates and Contact</h2>
            <p>
                We may update this Privacy Policy from time to time. If we make material changes, we will update the
                effective date above and may provide additional notice where required by law.
            </p>
            <p>
                For privacy questions or requests, use the
                <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.
            </p>
        </section>
    </section>
</x-layouts.public>
