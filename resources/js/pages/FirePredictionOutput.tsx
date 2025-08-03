import AuthLayout from '@/layouts/auth-layout';
import { Head } from '@inertiajs/react';
import React from 'react';

const FirePredictionOutput: React.FC = ({ fire_risk, error, probability }: { fire_risk?: string, error?: string, probability?: number }) => {

    return (
        <AuthLayout title="Fire Prediction Output" description="Processed fire prediction data output">
            <Head title="Output" />
            <div className="container mx-auto p-4">
                <h1 className="text-2xl font-bold mb-4">Processed Fire Prediction Data</h1>
                <div className="p-4 rounded whitespace-pre-wrap break-words text-gray-900 bg-gray-100">
                    {error ? (
                        <p>{error}</p>
                    ) : (
                        <>
                            <p>Fire Risk: {fire_risk}</p>
                            {probability !== undefined && (
                                <p>Probability: {(probability * 100).toFixed(2)}%</p>
                            )}
                        </>
                    )}
                </div>
            </div>
        </AuthLayout>
    );
};

export default FirePredictionOutput;
