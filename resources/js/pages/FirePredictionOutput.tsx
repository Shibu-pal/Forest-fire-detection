import AuthLayout from '@/layouts/auth-layout';
import { Head } from '@inertiajs/react';
import React from 'react';

const FirePredictionOutput: React.FC = ({ fire_risk,error }: { fire_risk?: string,error?: string }) => {

    return (
        <AuthLayout title="Fire Prediction Output" description="Processed fire prediction data output">
            <Head title="Output" />
            <div className="container mx-auto p-4">
                <h1 className="text-2xl font-bold mb-4">Processed Fire Prediction Data</h1>
                <pre className=" p-4 rounded whitespace-pre-wrap break-words text-gray-900 bg-gray-100">
                    {fire_risk}
                    {error}
                </pre>
            </div>
        </AuthLayout>
    );
};

export default FirePredictionOutput;
