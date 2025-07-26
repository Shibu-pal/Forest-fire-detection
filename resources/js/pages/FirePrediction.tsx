import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type FirePredictionForm = {
    temperature: number;
    humidity: number;
    wind_speed: number;
    vegetation_type: string;
    elevation: number;
};

export default function FirePrediction() {
    const { data, setData, post, processing, errors, reset } = useForm<FirePredictionForm>({
        temperature: 0,
        humidity: 0,
        wind_speed: 0,
        vegetation_type: '',
        elevation: 0,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('check'));
    };

    return (
        <AuthLayout title="Fire Prediction" description="Enter your details below to process fire prediction data">
            <Head title="Fire Prediction" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="temperature">Temperature</Label>
                        <Input
                            id="temperature"
                            type="number"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="temperature"
                            value={data.temperature}
                            onChange={(e) => setData('temperature', Number(e.target.value))}
                            disabled={processing}
                            placeholder="Temperature"
                        />
                        <InputError message={errors.temperature} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="humidity">Humidity</Label>
                        <Input
                            id="humidity"
                            type="number"
                            required
                            tabIndex={2}
                            autoComplete="humidity"
                            value={data.humidity}
                            onChange={(e) => setData('humidity', Number(e.target.value))}
                            disabled={processing}
                            placeholder="Humidity"
                        />
                        <InputError message={errors.humidity} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="wind_speed">Wind Speed</Label>
                        <Input
                            id="wind_speed"
                            type="number"
                            required
                            tabIndex={3}
                            autoComplete="new-wind_speed"
                            value={data.wind_speed}
                            onChange={(e) => setData('wind_speed', Number(e.target.value))}
                            disabled={processing}
                            placeholder="Wind Speed"
                        />
                        <InputError message={errors.wind_speed} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="vegetation_type">Vegetation Type</Label>
                        <Input
                            id="vegetation_type"
                            type="text"
                            required
                            tabIndex={4}
                            autoComplete="vegetation_type"
                            value={data.vegetation_type}
                            onChange={(e) => setData('vegetation_type', e.target.value)}
                            disabled={processing}
                            placeholder="Vegetation Type"
                        />
                        <InputError message={errors.vegetation_type} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="elevation">Elevation</Label>
                        <Input
                            id="elevation"
                            type="number"
                            required
                            tabIndex={5}
                            autoComplete="elevation"
                            value={data.elevation}
                            onChange={(e) => setData('elevation', Number(e.target.value))}
                            disabled={processing}
                            placeholder="Elevation"
                        />
                        <InputError message={errors.elevation} />
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={6} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Process Data
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
