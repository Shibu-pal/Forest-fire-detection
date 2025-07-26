import AuthLayout from '@/layouts/auth-layout';
import { Head } from '@inertiajs/react';
import React from 'react';

const FirePredictionOutput: React.FC = ({ fire_risk,error }: { fire_risk?: string,error?: string }) => {

    return (
        <AuthLayout title="Fire Prediction Output" description="Processed fire prediction data output">
            <Head title="Fire Prediction Output" />
            <div className="container mx-auto p-4">
                <h1 className="text-2xl font-bold mb-4">Processed Fire Prediction Data</h1>
                <pre className="bg-gray-100 p-4 rounded whitespace-pre-wrap break-words text-gray-900">
                    {fire_risk}
                    {error}
                </pre>
            </div>
        </AuthLayout>
    );
};

export default FirePredictionOutput;
