<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Create Laravel Package
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white p-6 shadow-md">

                <form action="{{ route('package.generate') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="vendor" class="block text-sm font-medium text-gray-700">Vendor Name</label>
                        <input type="text" name="vendor" id="vendor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., sazumme" required>
                    </div>

                    <div>
                        <label for="package" class="block text-sm font-medium text-gray-700">Package Name</label>
                        <input type="text" name="package" id="package" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., publication" required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Short description of the package..." required></textarea>
                    </div>

                    <div>
                        <label for="author_name" class="block text-sm font-medium text-gray-700">Author Name</label>
                        <input type="text" name="author_name" id="author_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="author_email" class="block text-sm font-medium text-gray-700">Author Email</label>
                        <input type="email" name="author_email" id="author_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div>
                        <label for="author_website" class="block text-sm font-medium text-gray-700">Author Website</label>
                        <input type="text" name="author_website" id="author_website" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="https://www.example.com">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Generate & Download Package
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
