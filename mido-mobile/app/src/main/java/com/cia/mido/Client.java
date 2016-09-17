package com.cia.mido;

import android.os.AsyncTask;
import android.util.Log;

import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

/**
 * Created by angelynz95 on 17-Sep-16.
 */
public class Client extends AsyncTask<Void, Void, Void> {
    private JSONObject request;
    private JSONObject response;
    private String method;
    private String url;

    public Client(String url, JSONObject request, String method) {
        this.url = url;
        this.request = request;
        this.method = method;
        response = null;
    }

    public JSONObject getResponse() {
        return response;
    }

    @Override
    protected Void doInBackground(Void... arg0) {
        try {
            URL url = new URL(this.url);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setDoOutput(true);
            conn.setDoInput(true);
            conn.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
            conn.setRequestProperty("Accept", "application/json");
            conn.setRequestMethod(method);

            OutputStream os = conn.getOutputStream();
            os.write(request.toString().getBytes("UTF-8"));
            os.close();

            InputStream in = new BufferedInputStream(conn.getInputStream());
            BufferedReader reader = new BufferedReader(new InputStreamReader(in));
            String input = reader.readLine();
            StringBuilder sb = new StringBuilder();
            sb.append(input);
            conn.disconnect();
            response = new JSONObject(sb.toString());
        } catch (Exception e) {
            e.printStackTrace();
            response = new JSONObject();
        }
        return null;
    }
}
