<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{ value: @entangle($getStatePath()) }"
        style="display:grid; grid-template-columns:1fr 1fr; gap:12px;"
    >

        {{-- ── Classic ── --}}
        <button
            type="button"
            @click="value = 'classic'"
            :class="value === 'classic'
                ? 'ring-2 ring-primary-600 border-primary-500 bg-primary-50 dark:bg-primary-950'
                : 'border-gray-200 dark:border-gray-700 hover:border-primary-400'"
            class="relative flex items-center gap-3 rounded-xl border-2 p-2.5 text-left transition focus:outline-none"
        >
            <span x-show="value === 'classic'"
                class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-primary-600 z-10">
                <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </span>

            {{-- Thumbnail --}}
            <svg viewBox="0 0 420 297" xmlns="http://www.w3.org/2000/svg"
                 style="width:130px; height:92px; flex:none; border-radius:6px; border:1px solid #e5e7eb; background:#FFFDFB;">
                <rect x="8" y="8" width="404" height="281" fill="none" stroke="#6C2E63" stroke-width="2.5"/>
                <rect x="16" y="16" width="388" height="265" fill="none" stroke="#C79A3A" stroke-width="1.2"/>
                <path d="M8 50V20C8 13 13 8 20 8h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M16 50V22c0-4 3-6 6-6h26" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M412 50V20C412 13 407 8 400 8h-30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M404 50V22c0-4-3-6-6-6h-26" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M8 247V277C8 284 13 289 20 289h30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M16 247V275c0 4 3 6 6 6h26" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M412 247V277C412 284 407 289 400 289h-30" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <path d="M404 247V275c0 4-3 6-6 6h-26" fill="none" stroke="#BF861A" stroke-width="1.4"/>
                <rect x="183" y="46" width="22" height="22" rx="4" fill="#6C2E63"/>
                <text x="194" y="62" text-anchor="middle" font-family="Georgia,serif" font-size="14" font-weight="bold" fill="#E0A431">Q</text>
                <text x="215" y="62" font-family="Georgia,serif" font-size="13" font-weight="700" fill="#6C2E63">Quiz</text>
                <text x="248" y="62" font-family="Georgia,serif" font-size="13" font-weight="700" fill="#BF861A">ora</text>
                <text x="210" y="95" text-anchor="middle" font-family="Georgia,serif" font-size="22" font-weight="700" fill="#6C2E63">Certificate of Achievement</text>
                <text x="210" y="116" text-anchor="middle" font-family="Georgia,serif" font-size="11" fill="#5E535B" font-style="italic">Verified proficiency in a Quizora assessment</text>
                <line x1="136" y1="132" x2="196" y2="132" stroke="#C79A3A" stroke-width="1" opacity="0.7"/>
                <rect x="206" y="127" width="8" height="8" fill="#C79A3A" transform="rotate(45 210 131)"/>
                <line x1="224" y1="132" x2="284" y2="132" stroke="#C79A3A" stroke-width="1" opacity="0.7"/>
                <text x="210" y="152" text-anchor="middle" font-family="Arial,sans-serif" font-size="9" fill="#938793">This is to certify that</text>
                <text x="210" y="183" text-anchor="middle" font-family="Georgia,serif" font-size="30" font-weight="600" fill="#261C23">Nikhil Kapoor</text>
                <line x1="100" y1="193" x2="320" y2="193" stroke="#EADFE6" stroke-width="1"/>
                <text x="210" y="210" text-anchor="middle" font-family="Arial,sans-serif" font-size="8.5" fill="#5E535B">has successfully completed the</text>
                <text x="210" y="222" text-anchor="middle" font-family="Arial,sans-serif" font-size="8.5" font-weight="700" fill="#6C2E63">PHP OOP Mastery Test</text>
                <text x="210" y="234" text-anchor="middle" font-family="Arial,sans-serif" font-size="8.5" fill="#5E535B">on 14 June 2026, scoring 88%</text>
                <circle cx="352" cy="249" r="28" fill="#6C2E63" stroke="#C79A3A" stroke-width="2"/>
                <circle cx="352" cy="249" r="21" fill="none" stroke="#EBD9A6" stroke-width="1" stroke-dasharray="3,2"/>
                <text x="352" y="244" text-anchor="middle" font-family="Arial,sans-serif" font-size="10" fill="#E0A431">★</text>
                <text x="352" y="257" text-anchor="middle" font-family="Georgia,serif" font-size="11" font-weight="700" fill="#fff">88%</text>
                <text x="352" y="267" text-anchor="middle" font-family="Arial,sans-serif" font-size="5" letter-spacing="1" fill="#EBD9A6">VERIFIED</text>
                <text x="76" y="258" text-anchor="middle" font-family="Georgia,serif" font-size="13" font-style="italic" fill="#6C2E63">Arjun Mehta</text>
                <line x1="30" y1="263" x2="122" y2="263" stroke="#261C23" stroke-width="0.8" opacity="0.4"/>
                <text x="76" y="273" text-anchor="middle" font-family="Arial,sans-serif" font-size="7" font-weight="700" fill="#261C23">Arjun Mehta</text>
                <text x="76" y="282" text-anchor="middle" font-family="Arial,sans-serif" font-size="6" fill="#938793">Quiz Author &amp; Instructor</text>
            </svg>

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">Classic</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 leading-snug">Elegant border &amp;<br>centered layout</p>
            </div>
        </button>

        {{-- ── Contemporary ── --}}
        <button
            type="button"
            @click="value = 'contemporary'"
            :class="value === 'contemporary'
                ? 'ring-2 ring-primary-600 border-primary-500 bg-primary-50 dark:bg-primary-950'
                : 'border-gray-200 dark:border-gray-700 hover:border-primary-400'"
            class="relative flex items-center gap-3 rounded-xl border-2 p-2.5 text-left transition focus:outline-none"
        >
            <span x-show="value === 'contemporary'"
                class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-primary-600 z-10">
                <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </span>

            {{-- Thumbnail --}}
            <svg viewBox="0 0 420 297" xmlns="http://www.w3.org/2000/svg"
                 style="width:130px; height:92px; flex:none; border-radius:6px; border:1px solid #e5e7eb; background:#FFFFFF;">
                <rect x="0" y="0" width="122" height="297" fill="#6C2E63"/>
                <rect x="10" y="16" width="22" height="22" rx="4" fill="#E0A431"/>
                <text x="21" y="32" text-anchor="middle" font-family="Georgia,serif" font-size="14" font-weight="800" fill="#4D2049">Q</text>
                <text x="38" y="32" font-family="Georgia,serif" font-size="13" font-weight="700" fill="#FBF4F8">Quiz</text>
                <text x="69" y="32" font-family="Georgia,serif" font-size="13" font-weight="700" fill="#E0A431">ora</text>
                <circle cx="61" cy="122" r="36" fill="#380d50" stroke="rgba(255,255,255,0.25)" stroke-width="1.5"/>
                <circle cx="61" cy="122" r="28" fill="none" stroke="rgba(235,217,166,0.6)" stroke-width="1" stroke-dasharray="3,2"/>
                <text x="61" y="114" text-anchor="middle" font-family="Arial,sans-serif" font-size="12" fill="#E0A431">★</text>
                <text x="61" y="128" text-anchor="middle" font-family="Georgia,serif" font-size="15" font-weight="700" fill="#fff">91%</text>
                <text x="61" y="138" text-anchor="middle" font-family="Arial,sans-serif" font-size="5" letter-spacing="1.5" fill="rgba(235,217,166,.7)">VERIFIED</text>
                <rect x="42" y="220" width="38" height="38" fill="none" stroke="rgba(255,255,255,.3)" stroke-width="1" rx="2"/>
                <rect x="45" y="223" width="8" height="8" fill="rgba(255,255,255,.55)"/>
                <rect x="55" y="223" width="8" height="8" fill="rgba(255,255,255,.55)"/>
                <rect x="45" y="233" width="8" height="8" fill="rgba(255,255,255,.55)"/>
                <rect x="55" y="231" width="6" height="6" fill="rgba(255,255,255,.3)"/>
                <text x="61" y="267" text-anchor="middle" font-family="Arial,sans-serif" font-size="6" font-weight="700" fill="#fff">Scan to verify</text>
                <text x="61" y="276" text-anchor="middle" font-family="Arial,sans-serif" font-size="5" fill="rgba(251,244,248,.7)">quizora.app</text>
                <text x="136" y="28" font-family="Arial,sans-serif" font-size="7.5" font-weight="700" letter-spacing="2" fill="#BF861A">CERTIFICATE OF COMPLETION</text>
                <text x="136" y="56" font-family="Georgia,serif" font-size="20" font-weight="700" fill="#6C2E63">Awarded for</text>
                <text x="136" y="78" font-family="Georgia,serif" font-size="20" font-weight="700" fill="#BF861A" font-style="italic">outstanding</text>
                <text x="246" y="78" font-family="Georgia,serif" font-size="20" font-weight="700" fill="#6C2E63"> performance</text>
                <text x="136" y="110" font-family="Arial,sans-serif" font-size="9" fill="#938793">Presented to</text>
                <text x="136" y="142" font-family="Georgia,serif" font-size="28" font-weight="600" fill="#261C23">Aisha Khan</text>
                <rect x="136" y="150" width="140" height="2" fill="#C79A3A"/>
                <text x="136" y="168" font-family="Arial,sans-serif" font-size="8.5" fill="#5E535B">for successfully completing the</text>
                <text x="136" y="180" font-family="Arial,sans-serif" font-size="8.5" font-weight="700" fill="#6C2E63">HR Aptitude &amp; Reasoning</text>
                <text x="136" y="192" font-family="Arial,sans-serif" font-size="8.5" fill="#5E535B">with a distinction-level score.</text>
                <text x="370" y="215" text-anchor="end" font-family="Georgia,serif" font-size="14" font-style="italic" fill="#6C2E63">Kavya Reddy</text>
                <line x1="232" y1="221" x2="370" y2="221" stroke="#261C23" stroke-width="0.8" opacity="0.4"/>
                <text x="370" y="231" text-anchor="end" font-family="Arial,sans-serif" font-size="7" font-weight="700" fill="#261C23">Kavya Reddy</text>
                <text x="370" y="240" text-anchor="end" font-family="Arial,sans-serif" font-size="6" fill="#938793">Quiz Author</text>
                <line x1="136" y1="255" x2="406" y2="255" stroke="#EADFE6" stroke-width="1"/>
                <text x="136" y="268" font-family="Arial,sans-serif" font-size="6.5" font-weight="700" letter-spacing="1" fill="#938793">SCORE</text>
                <text x="136" y="281" font-family="Georgia,serif" font-size="11" font-weight="600" fill="#261C23">91% · Distinction</text>
                <text x="236" y="268" font-family="Arial,sans-serif" font-size="6.5" font-weight="700" letter-spacing="1" fill="#938793">DATE ISSUED</text>
                <text x="236" y="281" font-family="Georgia,serif" font-size="11" font-weight="600" fill="#261C23">18 Jun 2026</text>
                <text x="336" y="268" font-family="Arial,sans-serif" font-size="6.5" font-weight="700" letter-spacing="1" fill="#938793">CERT ID</text>
                <text x="336" y="281" font-family="monospace" font-size="9" fill="#5E535B">QZ-HRA-91-8820</text>
            </svg>

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">Contemporary</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 leading-snug">Modern split-panel<br>layout</p>
            </div>
        </button>

    </div>
</x-dynamic-component>
