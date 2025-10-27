<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Introduction Section -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Icon Layout Testing Guide</h3>
            <p class="text-sm text-gray-600">This page helps validate icon sizing and layout patterns across different contexts. Use the table below to verify that icons are properly sized and aligned.</p>
        </div>

        <!-- Icon Examples Section -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Real-World Layout Examples</h3>

            <div class="space-y-4">
                <!-- Modal Layout Example -->
                <div class="border border-gray-100 rounded-lg p-4">
                    <h4 class="font-medium text-gray-800 mb-3">Modal Icon Layout</h4>
                    <div class="space-y-3">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="text-amber-500 h-3 w-3 flex-shrink-0 align-bottom" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-amber-800 leading-none align-middle">Warning message with properly sized icon (12px) that should be inline with text.</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="text-blue-500 h-3 w-3 flex-shrink-0 align-bottom" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-blue-800 leading-none align-middle">Info message with properly sized icon (12px) that should be inline with text.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Actions Example -->
                <div class="border border-gray-100 rounded-lg p-4">
                    <h4 class="font-medium text-gray-800 mb-3">Table Action Icons</h4>
                    <div class="flex gap-3">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="mr-1.5 h-4 w-4 inline align-bottom" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            Edit
                        </button>
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="mr-1.5 h-4 w-4 inline align-bottom" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Status Indicators Example -->
                <div class="border border-gray-100 rounded-lg p-4">
                    <h4 class="font-medium text-gray-800 mb-3">Status Indicators</h4>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="text-green-500 h-4 w-4 flex-shrink-0 align-middle" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-gray-700 leading-none align-middle">Success state with 16px icon</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="text-amber-500 h-4 w-4 flex-shrink-0 align-middle" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-gray-700 leading-none align-middle">Warning state with 16px icon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Icon Size Reference -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Icon Size Reference</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <svg class="mx-auto mb-2 text-gray-400 h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-xs text-gray-600">12px (h-3 w-3)</div>
                </div>
                <div class="text-center">
                    <svg class="mx-auto mb-2 text-gray-400 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-xs text-gray-600">16px (h-4 w-4)</div>
                </div>
                <div class="text-center">
                    <svg class="mx-auto mb-2 text-gray-400 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-xs text-gray-600">20px (h-5 w-5)</div>
                </div>
                <div class="text-center">
                    <svg class="mx-auto mb-2 text-gray-400 h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div class="text-xs text-gray-600">24px (h-6 w-6)</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>