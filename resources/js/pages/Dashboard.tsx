import { Head } from '@inertiajs/react';

import AppLayout from '@/layouts/AppLayout';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface Stat {
    title: string;
    value: string;
    desc: string;
}

interface Row {
    id: number;
    name: string;
    job: string;
    color: string;
}

interface DashboardProps {
    title: string;
    description: string;
    stats: Stat[];
    rows: Row[];
}

export default function Dashboard({
    title,
    description,
    stats,
    rows,
}: DashboardProps) {
    return (
        <AppLayout title={title}>
            <Head title={title} />

            <p className="mb-6 text-muted-foreground">{description}</p>

            <div className="grid gap-4 sm:grid-cols-3">
                {stats.map((stat) => (
                    <Card key={stat.title}>
                        <CardHeader>
                            <CardDescription>{stat.title}</CardDescription>
                            <CardTitle className="text-3xl">
                                {stat.value}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            {stat.desc}
                        </CardContent>
                    </Card>
                ))}
            </div>

            <Card className="mt-8">
                <CardContent className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-left text-muted-foreground">
                                <th className="py-2 pr-4">#</th>
                                <th className="py-2 pr-4">Name</th>
                                <th className="py-2 pr-4">Job</th>
                                <th className="py-2">Favorite Color</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.id} className="border-b last:border-0">
                                    <td className="py-2 pr-4 font-medium">
                                        {row.id}
                                    </td>
                                    <td className="py-2 pr-4">{row.name}</td>
                                    <td className="py-2 pr-4">{row.job}</td>
                                    <td className="py-2">{row.color}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
