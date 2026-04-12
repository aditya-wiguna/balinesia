<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobsController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $location = $request->get('location', '');
        $type = $request->get('type', '');

        $jobs = JobPosting::query()
            ->approved()
            ->active()
            ->notExpired()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('job_title', 'like', "%{$query}%")
                        ->orWhere('company_name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->when($location, function ($q) use ($location) {
                $q->where('location', 'like', "%{$location}%");
            })
            ->when($type, function ($q) use ($type) {
                $q->where('employment_type', $type);
            })
            ->latest('posted_date')
            ->paginate(12)
            ->withQueryString();

        $locations = JobPosting::approved()
            ->active()
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $employmentTypes = JobPosting::approved()
            ->active()
            ->select('employment_type')
            ->distinct()
            ->whereNotNull('employment_type')
            ->orderBy('employment_type')
            ->pluck('employment_type');

        return view('jobs.index', [
            'jobs' => $jobs,
            'locations' => $locations,
            'employmentTypes' => $employmentTypes,
            'query' => $query,
            'selectedLocation' => $location,
            'selectedType' => $type,
        ]);
    }

    public function show(JobPosting $job): View
    {
        abort_unless($job->is_approved && $job->is_active && ($job->expires_date === null || $job->expires_date->isFuture()), 404);

        $relatedJobs = JobPosting::query()
            ->approved()
            ->active()
            ->notExpired()
            ->where('id', '!=', $job->id)
            ->where(function ($q) use ($job) {
                $q->where('location', 'like', "%{$job->location}%")
                    ->orWhere('employment_type', $job->employment_type);
            })
            ->latest('posted_date')
            ->take(4)
            ->get();

        return view('jobs.show', [
            'job' => $job,
            'relatedJobs' => $relatedJobs,
        ]);
    }
}
