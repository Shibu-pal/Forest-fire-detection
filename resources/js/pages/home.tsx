import { Link } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout';

export default function Home() {
    return (
        <AuthLayout title="Forest Fire Prediction" description="Choose your preferred method to predict forest fires">
            <div className="container mx-auto p-4">
                <div className="grid grid-cols-1 md:grid-cols-1 gap-8 mt-8 ">
                    <div className="bg-white rounded-lg shadow-lg p-4 flex flex-col items-center text-center">
                        <div className="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h2 className="text-xl font-bold mb-2">Image Input</h2>
                        <p className="text-gray-600 mb-4">Upload a satelite image of a forest area to predict fire risk</p>
                        <Link 
                            href={route('imageform')}
                            className="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800 transition-colors"
                        >
                            Upload Image
                        </Link>
                    </div>
                    <div className="bg-white rounded-lg shadow-lg p-4 flex flex-col items-center text-center">
                        <div className="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h2 className="text-xl font-bold mb-2">Data Input</h2>
                        <p className="text-gray-600 mb-4">Enter environmental data to predict fire risk</p>
                        <Link 
                            href={route('dataform')}
                            className="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800 transition-colors"
                        >
                            Enter Data
                        </Link>
                    </div>
                </div>
                
                <div className="mt-12 text-center">
                    <h3 className="text-lg font-semibold mb-2">How It Works</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <div className="text-2xl font-bold text-gray-900 mb-2">1</div>
                            <p className="text-gray-600">Choose your input method (image or data)</p>
                        </div>
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <div className="text-2xl font-bold text-gray-900 mb-2">2</div>
                            <p className="text-gray-600">Provide the required information</p>
                        </div>
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <div className="text-2xl font-bold text-gray-900 mb-2">3</div>
                            <p className="text-gray-600">Get instant fire risk prediction</p>
                        </div>
                    </div>
                </div>
            </div>
        </AuthLayout>
    );
}
