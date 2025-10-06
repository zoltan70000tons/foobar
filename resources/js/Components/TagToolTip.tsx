import * as React from "react";
import Tooltip, { TooltipProps } from "@mui/material/Tooltip";
import { Stack, Typography } from "@mui/material";
import { Check, Close, Tag , Info} from "@mui/icons-material";

export interface TagToolTipProps {

    children: React.ReactElement;
    label: React.ReactNode;
    title: React.ReactNode;
    description?: React.ReactNode;
    placement?: TooltipProps["placement"];
    enterDelay?: number;
    leaveDelay?: number;
    maxWidth?: number | string;
    tooltipProps?: Omit<
        TooltipProps,
        "title" | "children" | "placement" | "enterDelay" | "leaveDelay"
    >;
}


const TagToolTip: React.FC<TagToolTipProps> = ({
    children,
    label,
    title,
    description,
    placement = "right",
    enterDelay = 300,
    leaveDelay = 0,
    maxWidth = 320,
    tooltipProps,
    ...props
}) => {

    console.log("description", description);
    const defaultTitle = (
        <Stack spacing={0.5}>
            <Typography variant="subtitle2" fontWeight={600} sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                <Info fontSize="inherit" /> {title ?? label}
            </Typography>
            <Typography variant="body2">{description}</Typography>
        </Stack>
    );
    return (
        <Tooltip arrow enterDelay={enterDelay} title={defaultTitle} {...props} placement={placement}>
            {children}
        </Tooltip>
    );
};

export default React.memo(TagToolTip);
