<x-filament-panels::page>

    {{-- Tabs Navigation --}}
    <div x-data="{ activeTab: 'backup' }" class="space-y-4">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'backup'" 
                    :class="{ 'border-primary-500 text-primary-600': activeTab === 'backup', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'backup' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Backup Manager
                </button>
                <button @click="activeTab = 'anomaly'" 
                    :class="{ 'border-primary-500 text-primary-600': activeTab === 'anomaly', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'anomaly' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Anomaly Detection
                </button>
                <button @click="activeTab = 'errors'" 
                    :class="{ 'border-primary-500 text-primary-600': activeTab === 'errors', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'errors' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Mobile Errors
                </button>
            </nav>
        </div>

        {{-- BACKUP TAB --}}
        <div x-show="activeTab === 'backup'" class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">Create New Backup</x-slot>
                <x-slot name="description">Select what you want to include in the backup ZIP file.</x-slot>

                <form wire:submit="createBackup" class="space-y-4">
                    <div class="flex gap-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="db" wire:model="backupOptions" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-gray-700 dark:text-gray-200">Database (SQL Dump)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="files" wire:model="backupOptions" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-gray-700 dark:text-gray-200">Public Storage (Images/Files)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="logs" wire:model="backupOptions" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-gray-700 dark:text-gray-200">Logs (System Logs)</span>
                        </label>
                    </div>

                    <div>
                        <x-filament::button type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createBackup">Create Backup Now</span>
                            <span wire:loading wire:target="createBackup">Creating Backup...</span>
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Recent Backups</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Filename</th>
                                <th scope="col" class="px-6 py-3">Size</th>
                                <th scope="col" class="px-6 py-3">Created At</th>
                                <th scope="col" class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->backups as $backup)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $backup['name'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 flex gap-2">
                                        <x-filament::button size="xs" color="success" wire:click="downloadBackup('{{ $backup['name'] }}')">
                                            Download
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:confirm="Are you sure?" wire:click="deleteBackup('{{ $backup['name'] }}')">
                                            Delete
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="4" class="px-6 py-4 text-center">No backups found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        {{-- ANOMALY TAB --}}
        <div x-show="activeTab === 'anomaly'" class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">System Health Check</x-slot>
                <div class="flex justify-between items-center mb-4">
                    <p class="text-sm text-gray-500">Scan your system for potential issues, misconfigurations, or data inconsistencies.</p>
                    <x-filament::button wire:click="scanAnomalies" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="scanAnomalies">Run Scan</span>
                        <span wire:loading wire:target="scanAnomalies">Scanning...</span>
                    </x-filament::button>
                </div>

                @if ($lastScanTime)
                    <div class="mb-4 text-xs text-gray-400">Last scan: {{ $lastScanTime }}</div>
                @endif

                <div class="space-y-4">
                    @forelse ($anomalies as $anomaly)
                        <div class="p-4 mb-4 text-sm rounded-lg flex items-start gap-3 
                            @if($anomaly['severity'] == 'danger') bg-red-50 text-red-800 dark:bg-gray-800 dark:text-red-400 border border-red-300
                            @elseif($anomaly['severity'] == 'warning') bg-yellow-50 text-yellow-800 dark:bg-gray-800 dark:text-yellow-300 border border-yellow-300
                            @else bg-green-50 text-green-800 dark:bg-gray-800 dark:text-green-400 border border-green-300 @endif">
                            
                            <div class="font-bold flex-shrink-0">
                                @if($anomaly['severity'] == 'danger') [CRITICAL]
                                @elseif($anomaly['severity'] == 'warning') [WARNING]
                                @else [OK] @endif
                            </div>
                            <div>
                                <span class="font-medium block">{{ $anomaly['title'] }}</span>
                                <div>{{ $anomaly['description'] }}</div>
                            </div>
                        </div>
                    @empty
                        @if ($lastScanTime)
                            <div class="p-4 text-sm text-gray-500 bg-gray-50 rounded-lg dark:bg-gray-800 dark:text-gray-400 text-center">
                                System scan completed. Use the button above to refresh results.
                            </div>
                        @else
                            <div class="p-4 text-sm text-gray-500 bg-gray-50 rounded-lg dark:bg-gray-800 dark:text-gray-400 text-center">
                                Click "Run Scan" to check for anomalies.
                            </div>
                        @endif
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- MOBILE ERROR TAB --}}
        <div x-show="activeTab === 'errors'" class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">Mobile Error Reports</x-slot>
                
                <div class="mb-4">
                    <input type="text" wire:model.live="searchQuery" placeholder="Search error..." class="w-full rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3">Error Message</th>
                                <th class="px-4 py-3">Device</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->mobileErrors as $error)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $error->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-red-600">{{ Str::limit($error->error_message, 50) }}</div>
                                        <div class="text-xs text-gray-400">{{ $error->app_version }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $error->device_info }}</td>
                                    <td class="px-4 py-3">
                                        @if($error->is_resolved)
                                            <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Resolved</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Open</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <x-filament::button size="xs" color="gray" 
                                            wire:click="openStackModal({{ $error->id }})">
                                            View Stack
                                        </x-filament::button>
                                        @if(!$error->is_resolved)
                                            <x-filament::button size="xs" color="success" wire:click="resolveError({{ $error->id }})">
                                                Resolve
                                            </x-filament::button>
                                        @endif
                                        <x-filament::button size="xs" color="danger" wire:click="deleteError({{ $error->id }})" wire:confirm="Delete this report?">
                                            X
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="5" class="px-6 py-4 text-center">No error reports found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $this->mobileErrors->links() }}
                </div>
            </x-filament::section>
        </div>

    </div>

    {{-- Stack Trace Modal --}}
    <x-filament::modal id="stack-trace" width="5xl">
        <x-slot name="heading">Stack Trace Detail</x-slot>
        <div class="p-4 bg-gray-900 text-gray-100 rounded-lg overflow-x-auto text-xs font-mono">
            <pre class="whitespace-pre-wrap">{{ $this->stackTraceView ?? 'No stack trace available.' }}</pre>
        </div>
        <x-slot name="footer">
             <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'stack-trace' })">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
