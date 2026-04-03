<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;

/**
 * Service for generating JSON-LD structured data
 * Improves SEO and enables rich snippets in search results
 */
class StructuredDataService
{
    /**
     * Generate JSON-LD for Organization
     */
    public static function getOrganization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
            'logo' => config('app.url') . '/logo.png',
            'description' => 'Find your perfect job opportunity',
            'sameAs' => [
                'https://twitter.com/' . config('app.twitter', ''),
                'https://facebook.com/' . config('app.facebook', ''),
                'https://linkedin.com/company/' . config('app.linkedin', ''),
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Support',
                'email' => config('mail.from.address'),
                'availableLanguage' => ['en', 'id'],
            ],
        ];
    }

    /**
     * Generate JSON-LD for Job Posting
     */
    public static function getJobPosting(Job $job): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $job->description,
            'datePosted' => $job->created_at->toIso8601String(),
            'validThrough' => $job->updated_at->addDays(30)->toIso8601String(),
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $job->company_name,
                'logo' => config('app.url') . '/logo.png',
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $job->location,
                    'addressCountry' => 'ID',
                ],
            ],
            'baseSalary' => [
                '@type' => 'PriceSpecification',
                'priceCurrency' => 'IDR',
                'price' => $job->salary,
            ],
            'jobBenefits' => [
                'Health Insurance',
                'Flexible Schedule',
                'Professional Development',
            ],
            'workHours' => 'Full-time',
            'employmentType' => $job->type,
        ];
    }

    /**
     * Generate JSON-LD for Person (User Profile)
     */
    public static function getUserProfile(User $user): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->phone ?? '',
            'image' => $user->profile_photo_path ?? '',
            'jobTitle' => $user->bio ?? 'Professional',
            'worksFor' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
            'sameAs' => [],
        ];
    }

    /**
     * Generate JSON-LD for BreadcrumbList
     */
    public static function getBreadcrumbs(array $breadcrumbs): array
    {
        $items = [];
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['label'],
                'item' => $breadcrumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Generate JSON-LD for AggregateRating (for job results)
     */
    public static function getAggregateRating(float $rating, int $reviewCount): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'AggregateRating',
            'ratingValue' => $rating,
            'reviewCount' => $reviewCount,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /**
     * Generate JSON-LD for WebPage (SEO metadata)
     */
    public static function getWebpage(string $title, string $description, string $url, ?string $image = null): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'image' => $image ?? config('app.url') . '/default-image.png',
            'inLanguage' => 'id',
            'isPartOf' => [
                '@type' => 'Website',
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],
        ];
    }
}
