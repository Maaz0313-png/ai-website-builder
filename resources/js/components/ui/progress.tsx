import * as React from 'react'
import { cn } from '@/lib/utils'

interface ProgressProps extends React.ProgressHTMLAttributes<HTMLProgressElement> {}

const Progress = React.forwardRef<HTMLProgressElement, ProgressProps>(
    ({ className, value, ...props }, ref) => {
        return (
            <progress
                ref={ref}
                className={cn(
                    'relative h-2 w-full overflow-hidden rounded-full bg-secondary',
                    className
                )}
                value={value}
                max={100}
                {...props}
            >
                <div
                    className={cn(
                        'h-full bg-primary transition-all duration-300 ease-out',
                        value === undefined && 'animate-pulse'
                    )}
                    style={{ width: `${value}%` }}
                />
            </progress>
        )
    }
)
Progress.displayName = 'Progress'

export { Progress }