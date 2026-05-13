import React from "react";
import "../css/loading-overlay.css";

export default function LoadingOverlay({ show, message }) {
    if (!show) return null;

    return (
        <div className="loading-overlay">
            <div className="loading-box">
                <div className="loading-spinner"></div>
                <p>{message}</p>
            </div>
        </div>
    );
}