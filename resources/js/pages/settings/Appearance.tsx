import { Head } from '@inertiajs/react';
import { Monitor, Moon, Sun } from 'lucide-react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { cn } from '@/lib/utils';
import { type Appearance, useAppearance } from '@/hooks/use-appearance';

const options: { value: Appearance; label: string; icon: typeof Sun }[] = [
    { value: 'light', label: 'ライト', icon: Sun },
    { value: 'dark', label: 'ダーク', icon: Moon },
    { value: 'system', label: 'システム', icon: Monitor },
];

export default function Appearance() {
    const { appearance, setAppearance } = useAppearance();

    return (
        <SettingsLayout title="外観">
            <Head title="外観設定" />

            <p className="mb-4 text-sm text-muted-foreground">
                アプリの配色を選択します。
            </p>

            <div className="grid grid-cols-3 gap-3">
                {options.map((option) => (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => setAppearance(option.value)}
                        className={cn(
                            'flex flex-col items-center gap-2 rounded-lg border p-4 text-sm transition-colors',
                            appearance === option.value
                                ? 'border-primary bg-accent'
                                : 'hover:bg-accent',
                        )}
                    >
                        <option.icon className="size-5" />
                        {option.label}
                    </button>
                ))}
            </div>
        </SettingsLayout>
    );
}
