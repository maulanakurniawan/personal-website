<x-layouts.public meta-title="Privacy Policy · Maulana Kurniawan" meta-description="Privacy policy for Maulana Kurniawan personal website.">
    <section class="mx-auto max-w-4xl space-y-5">
        <h1 class="text-4xl font-bold">Privacy Policy</h1>
        <p class="text-sm text-base-content/70">Effective date: {{ now()->format('F j, Y') }}</p>
        <p class="text-sm text-base-content/75">This website collects minimal personal data required to operate public pages and handle contact form submissions.</p>
        <h2 class="text-xl font-semibold">What data is collected</h2>
        <ul class="list-disc pl-5 text-sm text-base-content/75 space-y-1">
            <li>Basic analytics data for website performance.</li>
            <li>Contact form details (name, email, subject, and message).</li>
            <li>Technical logs for security and reliability.</li>
        </ul>
        <h2 class="text-xl font-semibold">How data is used</h2>
        <p class="text-sm text-base-content/75">Data is used to respond to inquiries, maintain site security, and improve website content and performance. Data is not sold.</p>
        <h2 class="text-xl font-semibold">Contact</h2>
        <p class="text-sm text-base-content/75">For privacy questions, use the <a href="{{ route('contact.show') }}" class="text-primary hover:underline">contact page</a>.</p>
    </section>
</x-layouts.public>
