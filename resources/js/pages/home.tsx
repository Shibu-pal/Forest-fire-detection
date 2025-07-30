import { cn } from "@/lib/utils";
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    temperature: string;
    humidity: string;
    wind_speed: string;
    vegetation_type: string;
    elevation: string;
};
export default function Home() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        temperature: '',
        humidity: '',
        wind_speed: '',
        vegetation_type: '',
        elevation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('check'));
    };
    return (
        <AuthLayout title="Forest fire" description="Enter your details below to know there fire in forest or not">
            <Head title="Home" />
            <div className="container mx-auto p-4 bg-gray-100">
                <form className="flex flex-col gap-6 relative" onSubmit={submit}>
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="temperature">Temperature</Label>
                            <Input
                                id="temperature"
                                type="string"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="temperature"
                                value={data.temperature}
                                onChange={(e) => setData('temperature', (e.target.value))}
                                disabled={processing}
                                placeholder="Temperature"
                            />
                            <InputError message={errors.temperature} className="mt-2" />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="humidity">Humidity</Label>
                            <Input
                                id="humidity"
                                type="string"
                                required
                                tabIndex={2}
                                autoComplete="humidity"
                                value={data.humidity}
                                onChange={(e) => setData('humidity', (e.target.value))}
                                disabled={processing}
                                placeholder="Humidity"
                            />
                            <InputError message={errors.humidity} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="wind_speed">Wind Speed</Label>
                            <Input
                                id="wind_speed"
                                type="string"
                                required
                                tabIndex={3}
                                autoComplete="new-wind_speed"
                                value={data.wind_speed}
                                onChange={(e) => setData('wind_speed', (e.target.value))}
                                disabled={processing}
                                placeholder="Wind Speed"
                            />
                            <InputError message={errors.wind_speed} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="vegetation_type">Vegetation Type</Label>
                            <select
                                id="vegetation_type"
                                required
                                tabIndex={4}
                                value={data.vegetation_type}
                                onChange={(e) => setData('vegetation_type', e.target.value)}
                                disabled={processing}
                                className={cn(
                                    "border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm bg-gray-900 text-gray-100",
                                    "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
                                    "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive"
                                )}
                            >
                                <option value="" disabled>
                                    Select Vegetation Type
                                </option>
                                <option value="deciduous">Coniferous</option>
                                <option value="deciduous">Deciduous</option>
                                <option value="grassland">Grassland</option>
                                <option value="bamboo">Bamboo</option>
                                <option value="mixed">Mixed</option>
                            </select>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="elevation">Elevation</Label>
                            <Input
                                id="elevation"
                                type="string"
                                required
                                tabIndex={5}
                                autoComplete="elevation"
                                value={data.elevation}
                                onChange={(e) => setData('elevation', (e.target.value))}
                                disabled={processing}
                                placeholder="Elevation"
                            />
                            <InputError message={errors.elevation} />
                        </div>
                        <Button type="submit" className="mt-2 w-full bg-gray-900 text-gray-100 hover:bg-gray-900 cursor-pointer hover:scale-105 transition-transform duration-200" tabIndex={6} disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Check Data
                        </Button>
                    </div>
                </form>
            </div>
        </AuthLayout>
    )
}
