import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type ImageForm = {
    image: File | null;
};

export default function ImageFirePrediction() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<ImageForm>>({
        image: null,
    });
    
    const [preview, setPreview] = useState<string | null>(null);

    const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            setData('image', file);
            const reader = new FileReader();
            reader.onload = (e) => {
                setPreview(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('checkimage'));
    };

    return (
        <AuthLayout title="Forest Fire Prediction" description="Upload an image to predict forest fire risk">
            <Head title="Image Prediction" />
            <div className="container mx-auto p-4 bg-gray-100">
                <form className="flex flex-col gap-6 relative" onSubmit={submit}>
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="image">Forest Image</Label>
                            <Input
                                id="image"
                                type="file"
                                accept="image/*"
                                required
                                autoFocus
                                tabIndex={1}
                                onChange={handleImageChange}
                                disabled={processing}
                                className="file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-white hover:file:bg-gray-700"
                            />
                            <InputError message={errors.image} className="mt-2" />
                        </div>

                        {preview && (
                            <div className="grid gap-2">
                                <Label>Image Preview</Label>
                                <div className="flex justify-center">
                                    <img 
                                        src={preview} 
                                        alt="Preview" 
                                        className="max-w-full max-h-64 object-contain border rounded-lg"
                                    />
                                </div>
                            </div>
                        )}

                        <Button 
                            type="submit" 
                            className="mt-2 w-full bg-gray-900 text-gray-100 hover:bg-gray-900 cursor-pointer hover:scale-105 transition-transform duration-200" 
                            tabIndex={6} 
                            disabled={processing}
                        >
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Predict Fire Risk
                        </Button>
                    </div>
                </form>
            </div>
        </AuthLayout>
    )
}
