import { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img 
            {...props}
            src="/Images/Icon.jpg" 
            alt="App Logo"
            className={props.className || "h-8 w-auto"}
        />
    );
}
