import { Head, Link } from '@inertiajs/react';

import AppLayout from '@/layouts/AppLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

interface Post {
    id: number;
    title: string;
    author: string;
}

interface Pagination {
    data: Post[];
    current_page: number;
    last_page: number;
}

interface PostsIndexProps {
    title: string;
    posts: Pagination;
}

export default function PostsIndex({ title, posts }: PostsIndexProps) {
    return (
        <AppLayout title={title}>
            <Head title={title} />

            {posts.data.length > 0 ? (
                <Card>
                    <CardContent className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="py-2 pr-4">ID</th>
                                    <th className="py-2 pr-4">Title</th>
                                    <th className="py-2">Author</th>
                                </tr>
                            </thead>
                            <tbody>
                                {posts.data.map((post) => (
                                    <tr
                                        key={post.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="py-2 pr-4 font-medium">
                                            {post.id}
                                        </td>
                                        <td className="py-2 pr-4">
                                            {post.title}
                                        </td>
                                        <td className="py-2">{post.author}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            ) : (
                <p className="text-muted-foreground">No posts yet.</p>
            )}

            <div className="mt-6 flex items-center justify-between">
                <Button
                    asChild
                    variant="outline"
                    size="sm"
                    disabled={posts.current_page <= 1}
                >
                    <Link href={`/posts?page=${posts.current_page - 1}`}>
                        Previous
                    </Link>
                </Button>
                <span className="text-sm text-muted-foreground">
                    {posts.current_page} / {posts.last_page}
                </span>
                <Button
                    asChild
                    variant="outline"
                    size="sm"
                    disabled={posts.current_page >= posts.last_page}
                >
                    <Link href={`/posts?page=${posts.current_page + 1}`}>
                        Next
                    </Link>
                </Button>
            </div>
        </AppLayout>
    );
}
