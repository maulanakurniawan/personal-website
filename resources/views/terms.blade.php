<x-layouts.public meta-title="User Terms of Service · SoloHours">
    <section class="mx-auto max-w-5xl space-y-10">
        <div class="max-w-3xl space-y-4">
            <p class="text-sm font-medium uppercase tracking-wide text-primary">Legal</p>
            <h1 class="text-4xl font-bold leading-tight md:text-5xl">Terms of Service</h1>
            <p class="text-base text-base-content/75">
                These Terms of Service ("Terms") govern your access to and use of SoloHours. By creating an account
                or using SoloHours, you agree to these Terms.
            </p>
            <p class="text-sm text-base-content/60">Last updated: {{ now()->format('F j, Y') }}</p>
        </div>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">1. Service Overview</h2>
            <p class="text-sm text-base-content/75">SoloHours is a hosted time-tracking service for freelancers and solo professionals.</p>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Client and project organization.</li>
                <li>Timer and manual time tracking.</li>
                <li>Billable/non-billable tracking and reporting.</li>
                <li>CSV exports and invoiced status workflows.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">2. Accounts and Security</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>You must provide accurate account information.</li>
                <li>You are responsible for your credentials and account activity.</li>
                <li>You must promptly notify us of suspected unauthorized use.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">3. Subscriptions and Billing</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>Paid features, pricing, and limits are described on the pricing and checkout pages.</li>
                <li>Paid subscriptions are processed through Paddle as Merchant of Record.</li>
                <li>Subscription status, renewals, cancellations, taxes, and refunds are governed by Paddle's terms and applicable law.</li>
                <li>Proration applies when you upgrade or downgrade a subscription, and billing adjustments are processed through Paddle.</li>
                <li>Subscriptions renew automatically unless cancelled before the renewal date through Paddle.</li>
                <li>If you cancel a paid subscription, cancellation handling, timing of access changes, and refund eligibility are managed through Paddle and subject to Paddle Buyer Terms and policies.</li>
                <li>SoloHours does not store full payment card details on its own servers.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">4. Refund Policy</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>SoloHours uses Paddle as Merchant of Record for all payments, billing, and refunds.</li>
                <li>Customers may request a refund within 14 days of purchase, subject to Paddle's refund policy and applicable law.</li>
                <li>Refund requests must be submitted through Paddle support channels.</li>
                <li>
                    For more information, see Paddle Buyer Terms:
                    <a href="https://www.paddle.com/legal/buyer-terms" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">https://www.paddle.com/legal/buyer-terms</a>.
                </li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3">
            <h2 class="text-xl font-semibold">5. Acceptable Use</h2>
            <ul class="list-disc space-y-1 pl-5 text-sm text-base-content/75">
                <li>No unauthorized access attempts, abuse, malware, or service interference.</li>
                <li>No use that violates law or third-party rights.</li>
                <li>No reverse engineering except where prohibited by law.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-4">
            <h2 class="text-xl font-semibold">6. Data, IP, and Service Changes</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-base-content/75">
                <li>You retain ownership of your content and data (including clients, projects, and time entries).</li>
                <li>You grant SoloHours a limited license to host, process, and transmit your data solely to operate, secure, and support the service.</li>
                <li>We may modify, add, or remove features to improve security, reliability, legal compliance, or product quality.</li>
                <li>We do not guarantee uninterrupted or error-free availability.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-4">
            <h2 class="text-xl font-semibold">7. Suspension, Termination, and Legal Terms</h2>
            <ul class="list-disc space-y-2 pl-5 text-sm text-base-content/75">
                <li>You may stop using SoloHours at any time.</li>
                <li>We may suspend or terminate accounts that violate these Terms or pose risk to SoloHours or other users.</li>
                <li>To the maximum extent permitted by law, SoloHours is provided "as is" and "as available."</li>
                <li>To the fullest extent permitted by law, liability is limited to direct damages up to amounts paid for SoloHours in the 12 months before the claim event.</li>
                <li>If any part of these Terms is unenforceable, the remaining provisions remain in effect.</li>
            </ul>
        </section>

        <section class="max-w-3xl space-y-3 pb-2 text-sm text-base-content/75">
            <h2 class="text-lg font-semibold text-base-content">8. Updates and Contact</h2>
            <p>
                We may update these Terms from time to time. If we make material changes, we will update the effective
                date above and may provide additional notice where required by law. Continued use of SoloHours after
                updates means you accept the revised Terms.
            </p>
            <p>
                For legal questions about these Terms, use the
                <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.
            </p>
        </section>
    </section>
</x-layouts.public>
