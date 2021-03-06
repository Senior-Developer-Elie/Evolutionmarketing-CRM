<?php
namespace App\Http\Helpers;

use App\Website;
use App\Blog;
use App\InnerBlog;

use Carbon\Carbon;

class BlogHelper {
    /**
     * Get Future Dates
     * @param int $months
     */
    public static function getFutureMonths()
    {
        $futureDates = [];

        for( $i = 0; $i < 12; $i++ ) {
            $futureDates[] = (Carbon::now())->startOfYear()->addMonths($i);
        }

        return $futureDates;
    }

    /**
     * Get Assigned Blog Clients
     * @param App\User $user
     */
    public static function getAssignedWebsites($user)
    {
        $query = Website::where('is_blog_client', 1)->where('archived', 0)->orderBy('name');

        if (! empty($user)) {
            $query->where('assignee_id', $user->id);
        }
        
        return $query->get();
    }

    /**
     * Get Done blogs For User
     * @param App\User $user
     */
    public static function getDoneBlogs($user = null)
    {
        $assignedWebsiteIds = array_column(self::getAssignedWebsites($user)->toArray(), 'id');
        return Blog::where('marked', 1)
                    ->whereIn('website_id', $assignedWebsiteIds)->where('name', '!=', 'N/A')->orderByDesc('desired_date')->get();
    }

    /**
     * Get Pending To Write Blogs For User
     * @param App\User $user
     */
    public static function getPendingToWriteBlogs($user = null)
    {
        $assignedWebsiteIds = array_column(self::getAssignedWebsites($user)->toArray(), 'id');
        return Blog::where(function ($subQuery) {
                        $subQuery->where('blog_url', '')
                            ->orWhereNull('blog_url');
                    })
                    ->where(function ($subQuery) {
                        $subQuery->where('blog_image', '')
                            ->orWhereNull('blog_image');
                    })->where('marked', '!=', '1')
                    ->whereIn('website_id', $assignedWebsiteIds)->where('name', '!=', 'N/A')->orderByDesc('desired_date')->get();
    }

    /**
     * Get Pending To Add Image Blogs For User
     * @param App\User $user
     */
    public static function getPendingToAddImageBlogs($user = null)
    {
        $assignedWebsiteIds = array_column(self::getAssignedWebsites($user)->toArray(), 'id');
        return Blog::whereNotNull('blog_url')
                    ->where('blog_url', '!=', '')
                    ->where(function ($subQuery) {
                        $subQuery->where('blog_image','')
                        ->orWhereNull('blog_image');
                    })->where('marked', '!=', '1')
                    ->whereIn('website_id', $assignedWebsiteIds)->where('name', '!=', 'N/A')->orderByDesc('desired_date')->get();
    }

    /**
     * Get Pending To Add To Website Blogs For User
     * @param App\User $user
     */
    public static function getPendingToAddToWebsiteBlogs($user = null)
    {
        $assignedWebsiteIds = array_column(self::getAssignedWebsites($user)->toArray(), 'id');
        return Blog::whereNotNull('blog_url')
                    ->where('blog_url', '!=', '')
                    ->whereNotNull('blog_image')
                    ->where('blog_image', '!=', '')
                    ->where('marked', '!=', '1')
                    ->whereIn('website_id', $assignedWebsiteIds)->where('name', '!=', 'N/A')->orderByDesc('desired_date')->get();
    }

    /**
     * Get Pending To Add Title Blogs For User
     * @param App\User $user
     */
    public static function getPendingToAddTitleBlogs($user = null)
    {
        $websites = BlogHelper::getAssignedWebsites($user);
        $futureMonths = self::getFutureMonths();

        $emptyBlogs = [];

        foreach( $websites as $website ){

            $futureBlogs = $website->futureBlogs();
            $availableMonths = $website->availableMonths();

            $prettyFutureBlogs = [];    //Fill out empty months as well

            foreach( $futureMonths as $index => $futureMonth ) {

                $blogExist = false;
                foreach( $futureBlogs as $blog ) {
                    $blogDate = (new Carbon($blog->desired_date))->startOfMonth();
                    if( $blogDate->diffInMonths($futureMonth) == 0 )    //If same month
                    {
                        $blogExist = $blog;
                        break;
                    }
                }

                if( $blogExist == false && in_array($futureMonth, $availableMonths) ) {
                    $emptyBlogs[] = [
                        'id'            => $blogExist === true ? $blogExist->id : -1,
                        'desired_date'  => $futureMonth,
                        'website'        => $website
                    ];
                }
                if( $blogExist == true && trim($blogExist->name) == '' ) {
                    $emptyBlogs[] = [
                        'id'            => $blogExist === true ? $blogExist->id : -1,
                        'desired_date'  => $futureMonth,
                        'website'        => $website
                    ];
                }
            }
        }
        return $emptyBlogs;
    }

    /**
     * Get Current Month Available blog Count
     */
    public static function getAvailableBlogCountThisMonth()
    {
        $count = 0;
        $websites = Website::get();
        foreach( $websites as $website ) {
            if( $website->is_blog_client ) {
                $blogExist = Blog::where('website_id', $website->id)
                                ->where('desired_date', (new Carbon('first day of this month'))->startOfDay())
                                ->get()->first();
                if( count($website->availableMonths(1)) > 0 )
                {
                    if( is_null($blogExist) || strtolower($blogExist->name) != 'n/a' )
                        $count++;
                }
                else if( !is_null($blogExist) && strtolower($blogExist->name) != 'n/a' )
                    $count++;

            }
        }
        return $count;
    }
}
